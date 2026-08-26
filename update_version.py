#!/usr/bin/env python3
"""
Azoogi Script Cache Version Manager
Usage:
  python update_version.py            # Auto-bumps version (e.g. 2.6 -> 2.7)
  python update_version.py bump       # Auto-bumps version
  python update_version.py 2.8        # Sets version explicitly to 2.8
  python update_version.py status     # Shows current version across HTML files
"""

import re
import sys
from pathlib import Path

PROJECT_ROOT = Path(__file__).parent.resolve()


def get_html_files():
    """Returns a list of HTML files in the project root."""
    return [f for f in PROJECT_ROOT.glob("*.html") if f.is_file()]


def find_current_version(html_files):
    """Finds the predominant ?v=X.Y version in HTML files."""
    versions = []
    pattern = re.compile(r'\?v=([0-9]+\.[0-9]+)')
    for filepath in html_files:
        try:
            content = filepath.read_text(encoding="utf-8")
            matches = pattern.findall(content)
            versions.extend(matches)
        except Exception:
            pass
    if not versions:
        return "1.0"
    from collections import Counter
    return Counter(versions).most_common(1)[0][0]


def increment_version(version_str):
    """Increments a version string like '2.6' to '2.7'."""
    parts = version_str.split('.')
    if len(parts) == 2 and parts[0].isdigit() and parts[1].isdigit():
        major, minor = int(parts[0]), int(parts[1])
        return f"{major}.{minor + 1}"
    return f"{version_str}.1"


def update_cache_version(target_version=None):
    """
    Updates ?v=X.Y query strings across all root HTML files.
    If target_version is None or 'bump', auto-increments current version.
    Returns (old_version, new_version, updated_files)
    """
    html_files = get_html_files()
    current_ver = find_current_version(html_files)

    if not target_version or target_version in ("bump", "auto"):
        new_ver = increment_version(current_ver)
    else:
        new_ver = target_version.lstrip('v')

    pattern = re.compile(r'\?v=[0-9]+\.[0-9]+')

    updated_files = []

    for filepath in html_files:
        content = filepath.read_text(encoding="utf-8")
        if pattern.search(content):
            new_content = pattern.sub(f'?v={new_ver}', content)
            if new_content != content:
                filepath.write_text(new_content, encoding="utf-8")
                updated_files.append(filepath.name)

    return current_ver, new_ver, updated_files


def main():
    arg = sys.argv[1] if len(sys.argv) > 1 else None

    if arg in ("status", "current", "show"):
        html_files = get_html_files()
        cur = find_current_version(html_files)
        print(f"Current script cache version across HTML files: v{cur}")
        return

    old_ver, new_ver, updated_files = update_cache_version(arg)
    print(f"Version update completed: v{old_ver} -> v{new_ver}")
    print(f"Updated {len(updated_files)} HTML file(s):")
    for fname in updated_files:
        print(f" - {fname}")


if __name__ == "__main__":
    main()
