#!/usr/bin/env python3
"""
Azoogi asset cache version manager.

Usage:
  python update_version.py            # Auto-bumps version (e.g. 2.6 -> 2.7)
  python update_version.py bump       # Auto-bumps version
  python update_version.py 2.8        # Sets version explicitly to 2.8
  python update_version.py status     # Shows current version
"""

from __future__ import annotations

import re
import sys
from pathlib import Path

PROJECT_ROOT = Path(__file__).parent.resolve()
ENV_PATH = PROJECT_ROOT / ".env"
ENV_EXAMPLE_PATH = PROJECT_ROOT / ".env.example"
VERSION_KEY = "ASSET_VERSION"
DEFAULT_VERSION = "2.10"


def read_env_version(path: Path) -> str | None:
    if not path.exists():
        return None
    match = re.search(rf"^{VERSION_KEY}=([0-9]+\.[0-9]+)\s*$", path.read_text(encoding="utf-8"), re.M)
    return match.group(1) if match else None


def write_env_version(path: Path, version: str) -> bool:
    if not path.exists():
        return False
    content = path.read_text(encoding="utf-8")
    pattern = re.compile(rf"^{VERSION_KEY}=.*$", re.M)
    replacement = f"{VERSION_KEY}={version}"
    if pattern.search(content):
        new_content = pattern.sub(replacement, content)
    else:
        new_content = content.rstrip() + f"\n{replacement}\n"
    if new_content != content:
        path.write_text(new_content, encoding="utf-8")
        return True
    return False


def find_current_version() -> str:
    return read_env_version(ENV_PATH) or read_env_version(ENV_EXAMPLE_PATH) or DEFAULT_VERSION


def increment_version(version_str: str) -> str:
    parts = version_str.split(".")
    if len(parts) == 2 and parts[0].isdigit() and parts[1].isdigit():
        return f"{int(parts[0])}.{int(parts[1]) + 1}"
    return f"{version_str}.1"


def update_cache_version(target_version=None):
    current_ver = find_current_version()
    if not target_version or target_version in ("bump", "auto"):
        new_ver = increment_version(current_ver)
    else:
        new_ver = str(target_version).lstrip("v")

    updated_files = []
    for path in (ENV_PATH, ENV_EXAMPLE_PATH):
        if write_env_version(path, new_ver):
            updated_files.append(path.name)

    return current_ver, new_ver, updated_files


def main() -> None:
    arg = sys.argv[1] if len(sys.argv) > 1 else None

    if arg in ("status", "current", "show"):
        print(f"Current script cache version: v{find_current_version()}")
        return

    old_ver, new_ver, updated_files = update_cache_version(arg)
    print(f"Version update completed: v{old_ver} -> v{new_ver}")
    print(f"Updated {len(updated_files)} file(s):")
    for fname in updated_files:
        print(f" - {fname}")


if __name__ == "__main__":
    main()
