#!/bin/bash

###############################################################################
# Script de suppression de toutes les candidatures
# ATTENTION: Ce script supprime TOUTES les candidatures et leurs documents
###############################################################################

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}  Suppression des candidatures${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

# Vérifier qu'on est dans le bon répertoire
if [ ! -f "artisan" ]; then
    echo -e "${RED}Erreur: Ce script doit être exécuté depuis le répertoire 'backend' du projet${NC}"
    exit 1
fi

# Demander confirmation
echo -e "${RED}ATTENTION: Cette action va supprimer TOUTES les candidatures et leurs documents!${NC}"
echo -e "${RED}Cette action est IRRÉVERSIBLE!${NC}"
echo ""
read -p "Êtes-vous sûr de vouloir continuer? (tapez 'OUI' en majuscules pour confirmer): " confirmation

if [ "$confirmation" != "OUI" ]; then
    echo -e "${YELLOW}Opération annulée.${NC}"
    exit 0
fi

echo ""
echo -e "${GREEN}Démarrage de la suppression...${NC}"
echo ""

# Créer une sauvegarde de la base de données
echo -e "${YELLOW}[1/5] Création d'une sauvegarde de la base de données...${NC}"
BACKUP_FILE="backup_$(date +%Y%m%d_%H%M%S).sql"

# Récupérer les informations de connexion depuis .env
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
DB_PORT=$(grep DB_PORT .env | cut -d '=' -f2)
DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)

if command -v mysqldump &> /dev/null; then
    mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_FILE" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Sauvegarde créée: $BACKUP_FILE${NC}"
    else
        echo -e "${RED}✗ Échec de la sauvegarde (mysqldump)${NC}"
    fi
else
    echo -e "${YELLOW}! mysqldump non disponible, sauvegarde ignorée${NC}"
fi

# Supprimer les fichiers documents du disque
echo -e "${YELLOW}[2/5] Suppression des fichiers documents du disque...${NC}"
DOCUMENTS_PATH="storage/app/documents"
if [ -d "$DOCUMENTS_PATH" ]; then
    FILES_COUNT=$(find "$DOCUMENTS_PATH" -type f | wc -l)
    echo "   Fichiers trouvés: $FILES_COUNT"
    
    # Créer une archive des fichiers avant suppression
    if [ $FILES_COUNT -gt 0 ]; then
        ARCHIVE_FILE="documents_backup_$(date +%Y%m%d_%H%M%S).tar.gz"
        tar -czf "$ARCHIVE_FILE" "$DOCUMENTS_PATH" 2>/dev/null
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓ Archive créée: $ARCHIVE_FILE${NC}"
        fi
        
        # Supprimer les fichiers
        rm -rf "$DOCUMENTS_PATH"/*
        echo -e "${GREEN}✓ Fichiers documents supprimés${NC}"
    else
        echo -e "${YELLOW}! Aucun fichier à supprimer${NC}"
    fi
else
    echo -e "${YELLOW}! Répertoire documents non trouvé${NC}"
fi

# Supprimer les enregistrements de documents de la base de données
echo -e "${YELLOW}[3/5] Suppression des enregistrements de documents...${NC}"
php artisan tinker --execute="
\$count = DB::table('application_documents')->count();
echo \"Documents en base: \$count\n\";
DB::table('application_documents')->delete();
echo \"✓ Documents supprimés\n\";
"

# Supprimer les candidatures de la base de données
echo -e "${YELLOW}[4/5] Suppression des candidatures...${NC}"
php artisan tinker --execute="
\$count = DB::table('merchant_applications')->count();
echo \"Candidatures en base: \$count\n\";
DB::table('merchant_applications')->delete();
echo \"✓ Candidatures supprimées\n\";
"

# Vider les caches
echo -e "${YELLOW}[5/5] Vidage des caches...${NC}"
php artisan cache:clear > /dev/null 2>&1
php artisan config:clear > /dev/null 2>&1
php artisan route:clear > /dev/null 2>&1
echo -e "${GREEN}✓ Caches vidés${NC}"

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Suppression terminée avec succès!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Fichiers de sauvegarde créés:${NC}"
if [ -f "$BACKUP_FILE" ]; then
    echo "  - Base de données: $BACKUP_FILE"
fi
if [ -f "$ARCHIVE_FILE" ]; then
    echo "  - Documents: $ARCHIVE_FILE"
fi
echo ""
echo -e "${YELLOW}Pour restaurer en cas de besoin:${NC}"
if [ -f "$BACKUP_FILE" ]; then
    echo "  mysql -h\$DB_HOST -u\$DB_USERNAME -p\$DB_PASSWORD \$DB_DATABASE < $BACKUP_FILE"
fi
if [ -f "$ARCHIVE_FILE" ]; then
    echo "  tar -xzf $ARCHIVE_FILE"
fi
echo ""
