#!/bin/bash

# Define plugin slug
PLUGIN_SLUG="redirect-manager-pro"
ZIP_NAME="${PLUGIN_SLUG}.zip"

# Get the current directory name to ensure we are in the right place
CURRENT_DIR_NAME=$(basename "$PWD")

if [ "$CURRENT_DIR_NAME" != "$PLUGIN_SLUG" ]; then
    echo "Error: Please run this script from the root of the '$PLUGIN_SLUG' directory."
    exit 1
fi

# Cleanup previous build
if [ -f "$ZIP_NAME" ]; then
    echo "Removing old build..."
    rm "$ZIP_NAME"
fi

echo "Building $ZIP_NAME..."

# Navigate to parent directory to zip the folder itself so it unzips with the folder
cd ..

# Zip command
# -r: recursive
# -q: quiet
# -x: exclude patterns
zip -r -q "$PLUGIN_SLUG/$ZIP_NAME" "$PLUGIN_SLUG" -x \
"*/.git/*" \
"*/.gitignore" \
"*/.DS_Store" \
"*/build.sh" \
"*/composer.json" \
"*/composer.lock" \
"*/node_modules/*" \
"*/tests/*" \
"*/bin/*" \
"*/.vscode/*" \
"*/.idea/*" \
"*/README.md" \
"*/task.md" \
"*/implementation_plan.md" \
"*/walkthrough.md"

# Navigate back
cd "$PLUGIN_SLUG"

if [ -f "$ZIP_NAME" ]; then
    echo "Build successful! File created: $ZIP_NAME"
else
    echo "Build failed."
    exit 1
fi
