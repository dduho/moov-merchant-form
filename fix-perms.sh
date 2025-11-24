cd /var/www/moov-merchant-form/backend
echo "Root@1324" | sudo -S chmod 755 storage/app/private/merchant-documents
echo "Root@1324" | sudo -S find storage/app/private/merchant-documents -type d -exec chmod 755 {} \;
echo "Root@1324" | sudo -S find storage/app/private/merchant-documents -type f -exec chmod 644 {} \;
echo "Permissions corrig?es!"
php /tmp/list-storage.php
