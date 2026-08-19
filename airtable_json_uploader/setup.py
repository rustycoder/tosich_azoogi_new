from setuptools import setup, find_packages

setup(
    name="airtable-json-uploader",
    version="0.1.0",
    packages=find_packages(),
    install_requires=[
        "requests>=2.28.0",
        "python-dotenv>=1.0.0",
        "flask>=2.0.0",
        "openpyxl>=3.0.0",
    ],

    entry_points={
        "console_scripts": [
            "airtable-upload = airtable_json_uploader.cli:main",
            "airtable-web = airtable_json_uploader.app:main",
            "airtable-extract = airtable_json_uploader.cli:extract_cli",
        ],
    },
    package_data={
        "airtable_json_uploader": ["templates/*.html", "static/*"],
    },
    include_package_data=True,
    author="Azoogi Team",
    description="A Python package to process JSON files, map keys to Airtable table columns, and update Airtable records via Web or CLI.",
)
