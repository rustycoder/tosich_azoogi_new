import urllib.request
import ssl

url = "https://file.colorsled.com/pro/catalog/product/678c9b8a43dbb.jpg"

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

req = urllib.request.Request(
    url, 
    headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}
)

try:
    with urllib.request.urlopen(req, context=ctx) as response:
        print(f"Status Code: {response.status}")
        print(f"Content Type: {response.getheader('Content-Type')}")
except Exception as e:
    print(f"Error fetching URL: {e}")
