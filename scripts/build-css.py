#!/usr/bin/env python3
"""
Build script to bundle and minify all CSS modules into styles/bundle.min.css.
Run this script whenever you edit any CSS file in styles/components/ or styles/sections/.
"""
import os
import re

ROOT_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
STYLES_DIR = os.path.join(ROOT_DIR, "styles")

ORDER = [
    "variables.css",
    "base.css",
    "layout.css",
    "components/buttons.css",
    "components/navigation.css",
    "components/cookie-banner.css",
    "sections/hero.css",
    "sections/home.css",
    "sections/team.css",
    "sections/tattoo.css",
    "sections/fineline.css",
    "sections/contact.css",
    "sections/contact-form.css",
    "sections/blog.css",
    "sections/piercings.css",
    "sections/legal.css",
    "i18n.css",
    "animations.css",
    "utilities.css"
]

def build():
    combined_blocks = []
    for rel_path in ORDER:
        full_path = os.path.join(STYLES_DIR, rel_path)
        if not os.path.exists(full_path):
            print(f"[WARN] File not found: {full_path}")
            continue
        with open(full_path, "r", encoding="utf-8") as f:
            content = f.read()

        # Adjust relative paths from subdirectories
        if "/" in rel_path:
            content = re.sub(r"\.\./\.\./images/", "../images/", content)
            content = re.sub(r"\.\./\.\./fonts/", "../fonts/", content)

        combined_blocks.append(
            f"/* ==========================================\n"
            f"   MODULE: {rel_path}\n"
            f"   ========================================== */\n\n"
            f"{content.strip()}"
        )

    bundle_css = "\n\n".join(combined_blocks) + "\n"

    # Write unminified bundle
    bundle_path = os.path.join(STYLES_DIR, "bundle.css")
    with open(bundle_path, "w", encoding="utf-8") as f:
        f.write(bundle_css)

    # Minify
    css = re.sub(r"/\*.*?\*/", "", bundle_css, flags=re.DOTALL)
    css = re.sub(r"\s+", " ", css)

    def protect_calc(match):
        inner = match.group(0)
        inner = inner.replace("+", " __PLUS__ ")
        inner = inner.replace("-", " __MINUS__ ")
        return inner

    css = re.sub(r"calc\([^)]+\)", protect_calc, css)
    css = re.sub(r"\s*([{}:;,>])\s*", r"\1", css)
    css = css.replace("__PLUS__", " + ")
    css = css.replace("__MINUS__", " - ")
    css = css.replace(";}", "}")
    minified_css = css.strip()

    min_path = os.path.join(STYLES_DIR, "bundle.min.css")
    with open(min_path, "w", encoding="utf-8") as f:
        f.write(minified_css)

    print(f"[OK] Generated {bundle_path} ({len(bundle_css.encode('utf-8')) / 1024:.1f} KB)")
    print(f"[OK] Generated {min_path} ({len(minified_css.encode('utf-8')) / 1024:.1f} KB)")

if __name__ == "__main__":
    build()
