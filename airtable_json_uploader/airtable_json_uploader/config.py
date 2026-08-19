import os
from pathlib import Path
from dotenv import load_dotenv, set_key

# Load environment variables from .env file
package_dir = Path(__file__).parent.parent
env_path = package_dir / ".env"
load_dotenv(dotenv_path=env_path)


def get_api_key() -> str:
    """Retrieve the Airtable Personal Access Token from environment variables."""
    key = os.getenv("AIRTABLE_API_KEY", "").strip()
    return key


def get_base_id() -> str:
    """Retrieve the Airtable Base ID from environment variables."""
    return os.getenv("AIRTABLE_BASE_ID", "").strip()


def get_products_table() -> str:
    """Retrieve Products table name from environment variables."""
    return os.getenv("AIRTABLE_PRODUCTS_TABLE", "Products").strip()


def get_categories_table() -> str:
    """Retrieve Categories table name from environment variables."""
    return os.getenv("AIRTABLE_CATEGORIES_TABLE", "Categories").strip()


def get_attributes_table() -> str:
    """Retrieve Attributes table name from environment variables."""
    return os.getenv("AIRTABLE_ATTRIBUTES_TABLE", "Attributes").strip()


def save_api_key(api_key: str) -> None:
    """Save the Airtable Personal Access Token securely to .env file."""
    api_key = api_key.strip()
    os.environ["AIRTABLE_API_KEY"] = api_key
    if not env_path.exists():
        env_path.touch()
    set_key(str(env_path), "AIRTABLE_API_KEY", api_key)


def save_config(api_key: str = None, base_id: str = None, products_table: str = None, categories_table: str = None, attributes_table: str = None) -> None:
    """Save configuration options to .env file."""
    if not env_path.exists():
        env_path.touch()

    if api_key is not None:
        os.environ["AIRTABLE_API_KEY"] = api_key.strip()
        set_key(str(env_path), "AIRTABLE_API_KEY", api_key.strip())
    if base_id is not None:
        os.environ["AIRTABLE_BASE_ID"] = base_id.strip()
        set_key(str(env_path), "AIRTABLE_BASE_ID", base_id.strip())
    if products_table is not None:
        os.environ["AIRTABLE_PRODUCTS_TABLE"] = products_table.strip()
        set_key(str(env_path), "AIRTABLE_PRODUCTS_TABLE", products_table.strip())
    if categories_table is not None:
        os.environ["AIRTABLE_CATEGORIES_TABLE"] = categories_table.strip()
        set_key(str(env_path), "AIRTABLE_CATEGORIES_TABLE", categories_table.strip())
    if attributes_table is not None:
        os.environ["AIRTABLE_ATTRIBUTES_TABLE"] = attributes_table.strip()
        set_key(str(env_path), "AIRTABLE_ATTRIBUTES_TABLE", attributes_table.strip())


def get_auth_headers() -> dict:
    """Return authorization headers for Airtable API requests."""
    key = get_api_key()
    if not key:
        raise ValueError("AIRTABLE_API_KEY is missing. Please set it in .env or via CLI.")
    return {
        "Authorization": f"Bearer {key}",
        "Content-Type": "application/json"
    }

