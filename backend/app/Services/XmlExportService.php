<?php

namespace App\Services;

use App\Models\PointOfSale;
use App\Models\Organization;
use Illuminate\Support\Collection;
use SimpleXMLElement;

class XmlExportService
{
    /**
     * Export validated PDVs to XML format.
     *
     * @param Collection|null $pdvs PDVs to export. If null, exports all validated PDVs.
     * @return string XML content
     */
    public function exportToXml(?Collection $pdvs = null): string
    {
        if ($pdvs === null) {
            $pdvs = PointOfSale::validated()->with(['organization', 'creator'])->get();
        }

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><PointsOfSale></PointsOfSale>');
        $xml->addAttribute('exportDate', now()->format('Y-m-d H:i:s'));
        $xml->addAttribute('count', (string) $pdvs->count());

        foreach ($pdvs as $pdv) {
            $pdvNode = $xml->addChild('PointOfSale');
            
            // Identifiants
            $pdvNode->addChild('ReferenceNumber', $this->escapeXml($pdv->reference_number));
            $pdvNode->addChild('UUID', $this->escapeXml($pdv->uuid));
            $pdvNode->addChild('Numero', $this->escapeXml($pdv->numero));
            
            // Dealer Info
            $dealerNode = $pdvNode->addChild('Dealer');
            $dealerNode->addChild('Name', $this->escapeXml($pdv->dealer_name));
            $dealerNode->addChild('NumeroFlooz', $this->escapeXml($pdv->numero_flooz));
            $dealerNode->addChild('Shortcode', $this->escapeXml($pdv->shortcode));
            if ($pdv->organization) {
                $dealerNode->addChild('OrganizationCode', $this->escapeXml($pdv->organization->code));
            }
            
            // PDV Info
            $pdvInfoNode = $pdvNode->addChild('PointInfo');
            $pdvInfoNode->addChild('NomPoint', $this->escapeXml($pdv->nom_point));
            $pdvInfoNode->addChild('Profil', $this->escapeXml($pdv->profil));
            $pdvInfoNode->addChild('TypeActivite', $this->escapeXml($pdv->type_activite));
            
            // Manager Info
            $managerNode = $pdvNode->addChild('Manager');
            $managerNode->addChild('FirstName', $this->escapeXml($pdv->firstname));
            $managerNode->addChild('LastName', $this->escapeXml($pdv->lastname));
            $managerNode->addChild('DateOfBirth', $pdv->date_of_birth?->format('Y-m-d'));
            $managerNode->addChild('Gender', $this->escapeXml($pdv->gender));
            $managerNode->addChild('IDDescription', $this->escapeXml($pdv->id_description));
            $managerNode->addChild('IDNumber', $this->escapeXml($pdv->id_number));
            $managerNode->addChild('IDExpiryDate', $pdv->id_expiry_date?->format('Y-m-d'));
            $managerNode->addChild('Nationality', $this->escapeXml($pdv->nationality));
            $managerNode->addChild('Profession', $this->escapeXml($pdv->profession));
            
            // Location
            $locationNode = $pdvNode->addChild('Location');
            $locationNode->addChild('Region', $this->escapeXml($pdv->region));
            $locationNode->addChild('Prefecture', $this->escapeXml($pdv->prefecture));
            $locationNode->addChild('Commune', $this->escapeXml($pdv->commune));
            $locationNode->addChild('Canton', $this->escapeXml($pdv->canton));
            $locationNode->addChild('Ville', $this->escapeXml($pdv->ville));
            $locationNode->addChild('Quartier', $this->escapeXml($pdv->quartier));
            $locationNode->addChild('Localisation', $this->escapeXml($pdv->localisation));
            
            // GPS
            $gpsNode = $pdvNode->addChild('GPS');
            $gpsNode->addChild('Latitude', (string) $pdv->latitude);
            $gpsNode->addChild('Longitude', (string) $pdv->longitude);
            $gpsNode->addChild('Accuracy', (string) $pdv->gps_accuracy);
            
            // Contacts
            $contactsNode = $pdvNode->addChild('Contacts');
            $contactsNode->addChild('NumeroProprietaire', $this->escapeXml($pdv->numero_proprietaire));
            $contactsNode->addChild('AutreContact', $this->escapeXml($pdv->autre_contact));
            
            // Fiscal
            $fiscalNode = $pdvNode->addChild('Fiscal');
            $fiscalNode->addChild('NIF', $this->escapeXml($pdv->nif));
            $fiscalNode->addChild('RegimeFiscal', $this->escapeXml($pdv->regime_fiscal));
            
            // Visibility
            $visibilityNode = $pdvNode->addChild('Visibility');
            $visibilityNode->addChild('Support', $this->escapeXml($pdv->support_visibilite));
            $visibilityNode->addChild('EtatSupport', $this->escapeXml($pdv->etat_support));
            
            // Other
            $pdvNode->addChild('NumeroCagnt', $this->escapeXml($pdv->numero_cagnt));
            
            // Dates
            $datesNode = $pdvNode->addChild('Dates');
            $datesNode->addChild('CreatedAt', $pdv->created_at?->format('Y-m-d H:i:s'));
            $datesNode->addChild('ValidatedAt', $pdv->validated_at?->format('Y-m-d H:i:s'));
        }

        return $xml->asXML();
    }

    /**
     * Export PDVs for a specific organization.
     *
     * @param int $organizationId
     * @return string XML content
     */
    public function exportByOrganization(int $organizationId): string
    {
        $pdvs = PointOfSale::validated()
            ->where('organization_id', $organizationId)
            ->with(['organization', 'creator'])
            ->get();

        return $this->exportToXml($pdvs);
    }

    /**
     * Export PDVs for a specific region.
     *
     * @param string $region
     * @return string XML content
     */
    public function exportByRegion(string $region): string
    {
        $pdvs = PointOfSale::validated()
            ->where('region', $region)
            ->with(['organization', 'creator'])
            ->get();

        return $this->exportToXml($pdvs);
    }

    /**
     * Escape XML special characters.
     *
     * @param string|null $value
     * @return string
     */
    protected function escapeXml(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }
}
