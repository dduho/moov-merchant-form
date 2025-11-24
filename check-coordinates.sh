#!/bin/bash
mysql -u moov_user -p'Moov@2024!' moov_merchant << EOF
SHOW COLUMNS FROM merchant_applications LIKE '%phone%';
EOF
