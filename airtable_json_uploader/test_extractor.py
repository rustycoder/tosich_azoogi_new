import tempfile
from pathlib import Path
import unittest
from unittest.mock import MagicMock, patch
from airtable_json_uploader.extractor import AirtableDataExtractor


class TestAirtableDataExtractor(unittest.TestCase):

    def setUp(self):
        self.patcher = patch("airtable_json_uploader.extractor.AirtableClient")
        self.mock_client_cls = self.patcher.start()
        self.mock_client = MagicMock()
        self.mock_client_cls.return_value = self.mock_client

        self.temp_dir = tempfile.TemporaryDirectory()

        self.extractor = AirtableDataExtractor(
            api_key="mock_pat_key",
            base_id="appTestBase123",
            products_table="Products",
            categories_table="Categories",
            attributes_table="Attributes"
        )
        # Override client with mock instance and isolate image directory to temp
        self.extractor.client = self.mock_client
        self.extractor.media_root = Path(self.temp_dir.name)

    def tearDown(self):
        self.patcher.stop()
        self.temp_dir.cleanup()

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
        self.assertEqual(parsed["CCT"], [{"value": "3000K", "icon": ""}, {"value": "4000K", "icon": ""}])
        self.assertEqual(parsed["IP Rating"], [{"value": "IP20", "icon": ""}])
        self.assertEqual(parsed["Power"], [{"value": "30W", "icon": ""}])
        self.assertEqual(parsed["Voltage"], [{"value": "24V", "icon": ""}])

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
        self.assertEqual(prod["product_images"], ["assets/img/products/recProd1_a2934e427d.jpg"])

        # Features from Attributes keys and linked records
        self.assertEqual(prod["product_features"]["CCT"], [{"value": "3000K", "icon": ""}, {"value": "4000K", "icon": ""}])
        self.assertEqual(prod["product_features"]["IP Rating"], [{"value": "IP68", "icon": ""}])
        self.assertEqual(prod["product_features"]["Power"], [{"value": "10W", "icon": ""}])
        self.assertEqual(prod["product_features"]["Warranty"], [{"value": "5 Years", "icon": ""}])

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

    def test_get_order_helper(self):
        self.assertEqual(AirtableDataExtractor._get_order({"Order": 1}), 1.0)
        self.assertEqual(AirtableDataExtractor._get_order({"Order": "2.5"}), 2.5)
        self.assertEqual(AirtableDataExtractor._get_order({"order": 3}), 3.0)
        self.assertEqual(AirtableDataExtractor._get_order({"Order": ""}), float('inf'))
        self.assertEqual(AirtableDataExtractor._get_order({"Order": None}), float('inf'))
        self.assertEqual(AirtableDataExtractor._get_order({}), float('inf'))
        self.assertEqual(AirtableDataExtractor._get_order(None), float('inf'))
        self.assertEqual(AirtableDataExtractor._get_order({"raw_fields": {"Order": 10}}), 10.0)

    def test_category_and_product_ordering_by_order_field(self):
        mock_categories = [
            {"id": "cat3", "fields": {"Name": "Category C", "Order": 3}},
            {"id": "cat1", "fields": {"Name": "Category A", "Order": 1}},
            {"id": "cat2", "fields": {"Name": "Category B", "Order": 2}},
            {"id": "cat_sub2", "fields": {"Name": "Sub B2", "Parent": ["cat2"], "Order": 20}},
            {"id": "cat_sub1", "fields": {"Name": "Sub B1", "Parent": ["cat2"], "Order": 10}},
            {"id": "cat_unset", "fields": {"Name": "Category Unset", "Order": ""}},
        ]
        mock_products = [
            {"id": "p3", "fields": {"Product_Name": "Prod 3 (Order 30)", "Category": "Category A", "Order": 30}},
            {"id": "p1", "fields": {"Product_Name": "Prod 1 (Order 10)", "Category": "Category A", "Order": 10}},
            {"id": "p2", "fields": {"Product_Name": "Prod 2 (Order 20)", "Category": "Category A", "Order": 20}},
            {"id": "p_empty", "fields": {"Product_Name": "Prod Empty Order", "Category": "Category A"}},
        ]

        def mock_fetch(base_id, table, **kwargs):
            if table == "Products":
                return list(mock_products)
            elif table == "Categories":
                return list(mock_categories)
            return []

        self.mock_client.fetch_existing_records.side_effect = mock_fetch

        catalog = self.extractor.run_extraction()

        # 1. Check products array ordering: p1 (10), p2 (20), p3 (30), p_empty (inf)
        product_names = [p["product_name"] for p in catalog["products"]]
        self.assertEqual(product_names, [
            "Prod 1 (Order 10)",
            "Prod 2 (Order 20)",
            "Prod 3 (Order 30)",
            "Prod Empty Order"
        ])

        # 2. Check top-level categories ordering in tree: Cat A (1), Cat B (2), Cat C (3), Cat Unset (inf)
        tree_root_names = [n["name"] for n in catalog["tree"]]
        self.assertEqual(tree_root_names, [
            "Category A",
            "Category B",
            "Category C",
            "Category Unset"
        ])

        # 3. Check subcategories under Category B: Sub B1 (10), Sub B2 (20)
        cat_b_node = next(n for n in catalog["tree"] if n["name"] == "Category B")
        sub_names = [sub["name"] for sub in cat_b_node["children"]]
        self.assertEqual(sub_names, ["Sub B1", "Sub B2"])

        # 4. Check all_categories list retains the tree sequence
        self.assertEqual(catalog["categories"][:4], [
            "Category A",
            "Category B",
            "Sub B1",
            "Sub B2"
        ])

    def test_attributes_ordering_by_order(self):
        mock_attributes = [
            {"id": "a3", "fields": {"Attribute_Name": "Power", "Attribute_Value": "50W", "Order": 3}},
            {"id": "a1", "fields": {"Attribute_Name": "CCT", "Attribute_Value": "3000K", "Order": 1}},
            {"id": "a2", "fields": {"Attribute_Name": "IP Rating", "Attribute_Value": "IP68", "Order": 2}},
            {"id": "a_unset", "fields": {"Attribute_Name": "Warranty", "Attribute_Value": "5Y"}},
        ]
        attr_index = self.extractor.extract_attributes(mock_attributes)
        
        # Keys in attr_index should be ordered a1, a2, a3, a_unset
        ordered_ids = list(attr_index.keys())
        self.assertEqual(ordered_ids, ["a1", "a2", "a3", "a_unset"])

    def test_multiple_categories_product_support(self):
        mock_categories = [
            {"id": "cat_pool", "fields": {"Name": "Pool Lights", "Order": 1}},
            {"id": "cat_outdoor", "fields": {"Name": "Outdoor & Architectural", "Order": 2}},
        ]
        mock_products = [
            {
                "id": "recMulti",
                "fields": {
                    "Product_Name": "Universal Underwater Light",
                    "Status": "publish",
                    "Category": ["cat_pool", "cat_outdoor"],
                    "Order": 1
                }
            }
        ]

        def mock_fetch(base_id, table, **kwargs):
            if table == "Products":
                return list(mock_products)
            elif table == "Categories":
                return list(mock_categories)
            return []

        self.mock_client.fetch_existing_records.side_effect = mock_fetch

        catalog = self.extractor.run_extraction()

        # Check product entry in catalog["products"]
        prod = next(p for p in catalog["products"] if p["id"] == "recMulti")
        self.assertEqual(prod["category"], "Pool Lights")
        self.assertEqual(prod["categories"], ["Pool Lights", "Outdoor & Architectural"])

        # Check product appears in BOTH category nodes in catalog["tree"]
        pool_node = next(n for n in catalog["tree"] if n["name"] == "Pool Lights")
        outdoor_node = next(n for n in catalog["tree"] if n["name"] == "Outdoor & Architectural")

        pool_prods = [c["name"] for c in pool_node["children"] if c["type"] == "product_row"]
        outdoor_prods = [c["name"] for c in outdoor_node["children"] if c["type"] == "product_row"]

        self.assertIn("Universal Underwater Light", pool_prods)
        self.assertIn("Universal Underwater Light", outdoor_prods)


class TestExcelAndJSONParser(unittest.TestCase):

    def test_is_sku_empty_variations(self):
        from airtable_json_uploader.parser import ExcelParser

        # Exact SKU match
        self.assertFalse(ExcelParser.is_sku_empty({"SKU": "PROD-101", "Name": "Product A"}))
        # SKU Code match
        self.assertFalse(ExcelParser.is_sku_empty({"SKU Code": "GL001", "Name": "Garden Light"}))
        # Product Code match
        self.assertFalse(ExcelParser.is_sku_empty({"Product Code": "GL001", "Name": "Garden Light"}))
        # Empty SKU Code
        self.assertTrue(ExcelParser.is_sku_empty({"SKU Code": "", "Name": "Garden Light"}))
        # None SKU Code
        self.assertTrue(ExcelParser.is_sku_empty({"SKU Code": None, "Name": "Garden Light"}))
        # Fallback with data but no SKU column
        self.assertFalse(ExcelParser.is_sku_empty({"Title": "Spotlight", "Price": 19.99}))
        # Empty row
        self.assertTrue(ExcelParser.is_sku_empty({"Title": "", "Price": None}))

    def test_skip_sku_in_sync_linked_attributes(self):
        from airtable_json_uploader.airtable_client import AirtableClient
        client = AirtableClient(api_key="mock_key")
        # Mock fetch_existing_records and list_tables
        client.fetch_existing_records = MagicMock(return_value=[])
        client.list_tables = MagicMock(return_value=[
            {
                "name": "Product attributes",
                "fields": [
                    {"name": "Attribute Name"},
                    {"name": "Term Name"}
                ]
            }
        ])
        def mock_create(base_id, table, records):
            return [{"id": f"rec_{i}"} for i in range(len(records))]
        client.create_records = MagicMock(side_effect=mock_create)

        attrs_input = {
            "Power": "12W",
            "SKU": "GL003",
            "CCT": "3000K"
        }
        client.sync_linked_attributes("appTestBase", "Product attributes", attrs_input)
        
        # Check created records
        created_records = client.create_records.call_args[0][2]
        attr_names = [r["fields"]["Attribute Name"] for r in created_records]
        self.assertIn("Power", attr_names)
        self.assertIn("CCT", attr_names)
        self.assertNotIn("SKU", attr_names)


if __name__ == "__main__":
    unittest.main()



