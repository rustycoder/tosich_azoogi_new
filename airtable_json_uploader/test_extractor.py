import unittest
from unittest.mock import MagicMock, patch
from airtable_json_uploader.extractor import AirtableDataExtractor


class TestAirtableDataExtractor(unittest.TestCase):

    def setUp(self):
        self.patcher = patch("airtable_json_uploader.extractor.AirtableClient")
        self.mock_client_cls = self.patcher.start()
        self.mock_client = MagicMock()
        self.mock_client_cls.return_value = self.mock_client

        self.extractor = AirtableDataExtractor(
            api_key="mock_pat_key",
            base_id="appTestBase123",
            products_table="Products",
            categories_table="Categories",
            attributes_table="Attributes"
        )
        # Override client with mock instance
        self.extractor.client = self.mock_client

    def tearDown(self):
        self.patcher.stop()

    def test_extract_image_urls(self):
        fields = {
            "Product_Name": "Test Light",
            "Images": [
                {"url": "https://example.com/img1.jpg", "filename": "img1.jpg"},
                {"url": "https://example.com/img2.jpg", "filename": "img2.jpg"}
            ]
        }
        urls = self.extractor.extract_image_urls(fields)
        self.assertEqual(urls, ["https://example.com/img1.jpg", "https://example.com/img2.jpg"])

    def test_parse_attribute_keys(self):
        attr_str = "CCT:3000K|CCT:4000K|IP Rating:IP20|Power:30W|Voltage:24V"
        parsed = AirtableDataExtractor.parse_attribute_keys(attr_str)
        self.assertEqual(parsed["CCT"], ["3000K", "4000K"])
        self.assertEqual(parsed["IP Rating"], "IP20")
        self.assertEqual(parsed["Power"], "30W")
        self.assertEqual(parsed["Voltage"], "24V")

    def test_run_extraction_mock_data(self):
        mock_products = [
            {
                "id": "recProd1",
                "fields": {
                    "Product_Name": "Neon Flex 360",
                    "Category": "Neon Lights",
                    "Product short description": "Short summary text",
                    "Product long description": "Full detailed description text",
                    "Attributes keys": "CCT:3000K|CCT:4000K|IP Rating:IP68|Power:10W",
                    "Options": '{"CCT": [{"name": "3000K", "id": "101"}, {"name": "4000K", "id": "102"}]}',
                    "Constraints": '[{"if": {"CCT": "3000K"}, "then": {"IP Rating": ["IP68"]}}]',
                    "Product_Images": [
                        {"url": "https://example.com/neon.jpg"}
                    ],
                    "Attributes": ["recAttr1"]
                }
            }
        ]
        mock_attributes = [
            {
                "id": "recAttr1",
                "fields": {
                    "Attribute_Name": "Warranty",
                    "Attribute_Value": "5 Years",
                    "Product": ["recProd1"]
                }
            }
        ]
        mock_categories = [
            {
                "id": "recCat1",
                "fields": {
                    "Category_Name": "Neon Lights"
                }
            }
        ]

        def mock_fetch(base_id, table):
            if table == "Products":
                return mock_products
            elif table == "Attributes":
                return mock_attributes
            elif table == "Categories":
                return mock_categories
            return []

        self.mock_client.fetch_existing_records.side_effect = mock_fetch

        catalog = self.extractor.run_extraction()

        self.assertEqual(len(catalog["products"]), 1)
        prod = catalog["products"][0]
        self.assertEqual(prod["product_name"], "Neon Flex 360")
        self.assertEqual(prod["category"], "Neon Lights")
        self.assertEqual(prod["product_short_description"], "Short summary text")
        self.assertEqual(prod["product_description"], "Full detailed description text")
        self.assertEqual(prod["product_images"], ["https://example.com/neon.jpg"])

        # Features from Attributes keys and linked records
        self.assertEqual(prod["product_features"]["CCT"], ["3000K", "4000K"])
        self.assertEqual(prod["product_features"]["IP Rating"], "IP68")
        self.assertEqual(prod["product_features"]["Power"], "10W")
        self.assertEqual(prod["product_features"]["Warranty"], "5 Years")

        # Configurator Options and Constraints
        self.assertIn("CCT", prod["options"])
        self.assertEqual(len(prod["options"]["CCT"]), 2)
        self.assertEqual(len(prod["constraints"]), 1)

    def test_parent_child_categories(self):
        mock_products = [
            {
                "id": "recP1",
                "fields": {
                    "Product_Name": "Cob Strip 24V",
                    "Category": ["recCatChild1"]
                }
            },
            {
                "id": "recP2",
                "fields": {
                    "Product_Name": "Neon Flex IP68",
                    "Category": ["recCatParent2"]
                }
            }
        ]
        mock_categories = [
            {
                "id": "recCatParent1",
                "fields": {
                    "Name": "LED Strip Lighting"
                }
            },
            {
                "id": "recCatChild1",
                "fields": {
                    "Name": "COB LED Strips",
                    "Parent": ["recCatParent1"]
                }
            },
            {
                "id": "recCatParent2",
                "fields": {
                    "Name": "Neon Flex"
                }
            }
        ]

        def mock_fetch(base_id, table):
            if table == "Products":
                return mock_products
            elif table == "Categories":
                return mock_categories
            return []

        self.mock_client.fetch_existing_records.side_effect = mock_fetch

        catalog = self.extractor.run_extraction()

        # Tree structure check
        tree = catalog["tree"]
        self.assertEqual(len(tree), 2)
        
        # Parent 1: LED Strip Lighting should have child COB LED Strips
        parent1_node = next(n for n in tree if n["name"] == "LED Strip Lighting")
        self.assertEqual(len(parent1_node["children"]), 1)
        self.assertEqual(parent1_node["children"][0]["name"], "COB LED Strips")
        self.assertEqual(parent1_node["children"][0]["children"][0]["name"], "Cob Strip 24V")

        # Parent 2: Neon Flex has no child categories, direct product
        parent2_node = next(n for n in tree if n["name"] == "Neon Flex")
        self.assertEqual(parent2_node["children"][0]["name"], "Neon Flex IP68")


if __name__ == "__main__":
    unittest.main()

