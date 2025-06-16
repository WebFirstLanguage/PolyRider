#!/usr/bin/env python3
"""
Logbie Framework Documentation Combiner

This script combines all Markdown (.md) files from the Docs directory into a single
consolidated file with clear section markers, preserving original formatting.

Features:
- Inserts clear beginning and end markers for each original file
- Includes file identifiers/names for each section
- Preserves the original content formatting
- Handles potential encoding issues
- Creates appropriate output file naming
- Includes error handling for missing directories or files
- Adds a timestamp of when the consolidation occurred
- Provides a simple progress indicator during processing
"""

import os
import sys
import datetime
import time
import re

# Configuration
DOCS_DIR = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "Docs")
OUTPUT_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUTPUT_FILENAME = "combined_documentation.md"
ENCODING = "utf-8"

def print_progress(current, total, prefix='Progress:', suffix='Complete', length=50, fill='█'):
    """
    Display a progress bar in the console
    
    Args:
        current (int): Current progress value
        total (int): Total value
        prefix (str): Prefix string
        suffix (str): Suffix string
        length (int): Bar length
        fill (str): Bar fill character
    """
    percent = ("{0:.1f}").format(100 * (current / float(total)))
    filled_length = int(length * current // total)
    bar = fill * filled_length + '-' * (length - filled_length)
    sys.stdout.write(f'\r{prefix} |{bar}| {percent}% {suffix}')
    sys.stdout.flush()
    if current == total:
        print()

def find_markdown_files(directory):
    """
    Find all Markdown files in the specified directory
    
    Args:
        directory (str): Directory path to search
        
    Returns:
        list: List of markdown file paths
    """
    if not os.path.exists(directory):
        raise FileNotFoundError(f"Directory not found: {directory}")
    
    markdown_files = []
    
    try:
        for file in os.listdir(directory):
            if file.lower().endswith('.md'):
                markdown_files.append(os.path.join(directory, file))
        
        # Sort files alphabetically
        markdown_files.sort()
        
        if not markdown_files:
            print(f"Warning: No Markdown files found in {directory}")
        
        return markdown_files
    
    except Exception as e:
        raise Exception(f"Error finding Markdown files: {str(e)}")

def read_file_content(file_path):
    """
    Read file content with encoding handling
    
    Args:
        file_path (str): Path to the file
        
    Returns:
        str: File content
    """
    if not os.path.exists(file_path):
        raise FileNotFoundError(f"File not found: {file_path}")
    
    try:
        with open(file_path, 'r', encoding=ENCODING) as file:
            return file.read()
    except UnicodeDecodeError:
        # Try with different encodings if UTF-8 fails
        encodings = ['latin-1', 'windows-1252', 'ascii']
        for encoding in encodings:
            try:
                with open(file_path, 'r', encoding=encoding) as file:
                    return file.read()
            except UnicodeDecodeError:
                continue
        
        raise UnicodeDecodeError(f"Failed to decode {file_path} with multiple encodings")
    except Exception as e:
        raise Exception(f"Error reading file {file_path}: {str(e)}")

def get_file_title(file_path, content):
    """
    Extract title from file content or use filename
    
    Args:
        file_path (str): Path to the file
        content (str): File content
        
    Returns:
        str: File title
    """
    # Try to extract title from first heading
    match = re.search(r'^#\s+(.+)$', content, re.MULTILINE)
    if match:
        return match.group(1).strip()
    
    # If no heading found, use filename without extension
    return os.path.splitext(os.path.basename(file_path))[0].replace('-', ' ').replace('_', ' ').title()

def combine_markdown_files(files):
    """
    Combine multiple markdown files into a single document
    
    Args:
        files (list): List of file paths
        
    Returns:
        str: Combined content
    """
    combined_content = []
    timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    
    # Add header with timestamp
    combined_content.append(f"# Logbie Framework - Combined Documentation\n\n")
    combined_content.append(f"*Generated on: {timestamp}*\n\n")
    combined_content.append("## Table of Contents\n\n")
    
    # Create table of contents
    toc = []
    for i, file_path in enumerate(files):
        try:
            content = read_file_content(file_path)
            title = get_file_title(file_path, content)
            toc.append(f"{i+1}. [{title}](#{title.lower().replace(' ', '-').replace('.', '').replace(':', '')})")
        except Exception as e:
            toc.append(f"{i+1}. [Error: {os.path.basename(file_path)}](#error-{i+1})")
            print(f"Warning: {str(e)}")
    
    combined_content.append("\n".join(toc) + "\n\n")
    combined_content.append("---\n\n")
    
    # Process each file
    for i, file_path in enumerate(files):
        try:
            print_progress(i, len(files), prefix='Processing files:', suffix=f'({i}/{len(files)})')
            
            content = read_file_content(file_path)
            title = get_file_title(file_path, content)
            filename = os.path.basename(file_path)
            
            # Add file section with clear markers
            combined_content.append(f"<!-- BEGIN: {filename} -->\n\n")
            combined_content.append(f"# {title}\n\n")
            combined_content.append(f"*Source: `{filename}`*\n\n")
            
            # Add content (skip the first heading if it matches the title)
            if content.lstrip().startswith('# '):
                # Skip the first heading line
                # Skip the first heading line using named parameters
                content = re.sub(pattern=r'^#\s+.+\n', repl='', string=content.lstrip(), count=1)
            
            combined_content.append(content.strip())
            combined_content.append("\n\n")
            combined_content.append("<!-- END: {filename} -->\n\n")
            combined_content.append("---\n\n")
            
            # Small delay to make progress visible
            time.sleep(0.1)
            
        except Exception as e:
            combined_content.append(f"<!-- BEGIN: ERROR - {filename} -->\n\n")
            combined_content.append(f"# Error: Could not process {filename}\n\n")
            combined_content.append(f"Error details: {str(e)}\n\n")
            combined_content.append(f"<!-- END: ERROR - {filename} -->\n\n")
            combined_content.append("---\n\n")
            print(f"Error processing {file_path}: {str(e)}")
    
    print_progress(len(files), len(files), prefix='Processing files:', suffix=f'({len(files)}/{len(files)})')
    
    return "".join(combined_content)

def write_output_file(content, output_path):
    """
    Write content to output file
    
    Args:
        content (str): Content to write
        output_path (str): Output file path
    """
    try:
        with open(output_path, 'w', encoding=ENCODING) as file:
            file.write(content)
        print(f"Successfully created: {output_path}")
    except Exception as e:
        raise Exception(f"Error writing output file: {str(e)}")

def main():
    """Main function to run the script"""
    try:
        print("Logbie Documentation Combiner")
        print("============================")
        
        # Ensure output directory exists
        if not os.path.exists(OUTPUT_DIR):
            os.makedirs(OUTPUT_DIR)
        
        # Find all markdown files
        print(f"Searching for Markdown files in: {DOCS_DIR}")
        markdown_files = find_markdown_files(DOCS_DIR)
        print(f"Found {len(markdown_files)} Markdown files")
        
        if not markdown_files:
            print("No files to process. Exiting.")
            return
        
        # Combine files
        print("Combining files...")
        combined_content = combine_markdown_files(markdown_files)
        
        # Generate output filename with date
        date_str = datetime.datetime.now().strftime("%Y%m%d")
        output_filename = f"logbie_docs_{date_str}.md"
        output_path = os.path.join(OUTPUT_DIR, output_filename)
        
        # Write output file
        print(f"Writing combined content to: {output_path}")
        write_output_file(combined_content, output_path)
        
        print("\nDocumentation combination completed successfully!")
        print(f"Total files processed: {len(markdown_files)}")
        print(f"Output file: {output_path}")
        
    except Exception as e:
        print(f"Error: {str(e)}")
        return 1
    
    return 0

if __name__ == "__main__":
    sys.exit(main())