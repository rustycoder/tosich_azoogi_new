import json
import os
import ssl
import urllib.request
from urllib.parse import urlparse

# Create an unverified SSL context to bypass SSL certificate verification errors
ssl_context = ssl._create_unverified_context()


def download_images_from_json(json_file_path):
    """Parses a JSON file and downloads all images in 'product_images' to the JSON file's folder."""
    try:
        with open(json_file_path, "r", encoding="utf-8") as f:
            json_data = json.load(f)
    except Exception as e:
        print(f"Error reading JSON file '{json_file_path}': {e}")
        return

    if not isinstance(json_data, dict):
        return

    image_urls = json_data.get("product_images", [])
    if not image_urls or not isinstance(image_urls, list):
        return

    target_dir = os.path.dirname(os.path.abspath(json_file_path))
    headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"}

    print(f"\nProcessing JSON file: {json_file_path}")

    for url in image_urls:
        if not isinstance(url, str) or not url.strip():
            continue

        parsed_url = urlparse(url)
        filename = os.path.basename(parsed_url.path)

        if not filename:
            continue

        out_path = os.path.join(target_dir, filename)

        if os.path.exists(out_path):
            print(f"Skipping {filename} (already exists)")
            continue

        print(f"Downloading {filename} to {target_dir}...")

        try:
            req = urllib.request.Request(url, headers=headers)
            with (
                urllib.request.urlopen(req, context=ssl_context) as response,
                open(out_path, "wb") as out_file,
            ):
                out_file.write(response.read())
            print(f"Successfully saved {filename}")
        except Exception as e:
            print(f"Failed to download {url}. Error: {e}")


def process_all_json_files(base_dir):
    """Recursively walks through base_dir and processes all .json files."""
    print(f"Starting search for .json files in: {base_dir}")
    json_count = 0
    for root, _, files in os.walk(base_dir):
        for file in files:
            if file.lower().endswith(".json"):
                json_path = os.path.join(root, file)
                json_count += 1
                download_images_from_json(json_path)

    print(f"\nFinished processing {json_count} JSON file(s).")


if __name__ == "__main__":
    # Target directory is the directory where this script is located
    base_directory = os.path.dirname(os.path.abspath(__file__))
    process_all_json_files(base_directory)