#!/bin/bash

# Check if a file was provided
if [ -z "$1" ]; then
    echo "Usage: $0 <image_file.png>"
    exit 1
fi

SOURCE_FILE="$1"
FILENAME=$(basename -- "$SOURCE_FILE")
EXTENSION="${FILENAME##*.}"
FILENAME_NO_EXT="${FILENAME%.*}"

# Array of target sizes
SIZES=(128 64 48 24 16)

echo "Resizing $SOURCE_FILE..."

for SIZE in "${SIZES[@]}"; do
    OUTPUT_NAME="${FILENAME_NO_EXT}-icon-${SIZE}x${SIZE}.${EXTENSION}"
    
    # sips --resampleHeightWidth [height] [width] [input_file] --out [output_file]
    sips -z "$SIZE" "$SIZE" "$SOURCE_FILE" --out "$OUTPUT_NAME" > /dev/null 2>&1
    
    echo "Created: $OUTPUT_NAME"
done

echo "Done!"