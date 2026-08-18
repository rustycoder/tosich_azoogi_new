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


def save_api_key(api_key: str) -> None:
    """Save the Airtable Personal Access Token securely to .env file."""
    api_key = api_key.strip()
    os.environ["AIRTABLE_API_KEY"] = api_key
    if not env_path.exists():
        env_path.touch()
    set_key(str(env_path), "AIRTABLE_API_KEY", api_key)


def get_auth_headers() -> dict:
    """Return authorization headers for Airtable API requests."""
    key = get_api_key()
    if not key:
        raise ValueError("AIRTABLE_API_KEY is missing. Please set it in .env or via CLI.")
    return {
        "Authorization": f"Bearer {key}",
        "Content-Type": "application/json"
    }
