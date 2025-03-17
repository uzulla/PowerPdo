#!/bin/bash

# Run the demo with PHP 7.4 (lowest supported version)
echo "Running demo with PHP 7.4..."
php7.4 index.php

# Clean up SQLite database
if [ -f demo.sqlite ]; then
    rm demo.sqlite
fi
