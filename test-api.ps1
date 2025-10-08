$headers = @{
    "Accept" = "application/json"
}

$body = @{
    first_name = "David"
    last_name = "DUHO"
    birth_date = "1995-10-12"
    birth_place = "Lomé"
    gender = "M"
    nationality = "TOGOLAISE"
    phone = "99669582"
    merchant_phone = "99665442"
    address = "Test address"
    id_type = "cni"
    id_number = "123456789"
    id_expiry_date = "2026-12-31"
    has_anid_card = "0"
    is_foreigner = "0"
    business_name = "Test Business"
    business_type = "pharmacie"
    business_address = "Test business address"
    usage_type = "MERC"
    has_cfe = "0"
    has_nif = "0"
    signature = "test-signature"
    accept_terms = "1"
}

try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/merchant-applications" -Method POST -Headers $headers -Body $body
    Write-Host "SUCCESS!"
    Write-Host ($response | ConvertTo-Json -Depth 5)
} catch {
    Write-Host "ERROR:" $_.Exception.Response.StatusCode
    if ($_.ErrorDetails) {
        Write-Host "Details:" $_.ErrorDetails.Message
    }
}