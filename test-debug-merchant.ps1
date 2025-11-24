# Test actual merchant application creation with documents

$uri = "http://127.0.0.1:8000/api/test-merchant-application"  # Use public test route
$filePath = "c:\Users\dduho\Documents\FLOOZ\Projets-Web\moov-merchant-form\test-document.txt"

# Create multipart form data
$boundary = [System.Guid]::NewGuid().ToString()
$LF = "`r`n"

# Read file content
$fileBytes = [System.IO.File]::ReadAllBytes($filePath)

# Build form data with required fields and documents
$bodyLines = @(
    "--$boundary",
    "Content-Disposition: form-data; name=`"last_name`"$LF",
    "TestUser",
    "--$boundary",
    "Content-Disposition: form-data; name=`"first_name`"$LF",
    "Debug", 
    "--$boundary",
    "Content-Disposition: form-data; name=`"birth_date`"$LF",
    "1990-01-01",
    "--$boundary",
    "Content-Disposition: form-data; name=`"birth_place`"$LF",
    "Lomé",
    "--$boundary",
    "Content-Disposition: form-data; name=`"gender`"$LF",
    "M",
    "--$boundary",
    "Content-Disposition: form-data; name=`"nationality`"$LF",
    "Togolaise",
    "--$boundary",
    "Content-Disposition: form-data; name=`"phone`"$LF",
    "99123456",
    "--$boundary",
    "Content-Disposition: form-data; name=`"address`"$LF",
    "123 Test Street",
    "--$boundary",
    "Content-Disposition: form-data; name=`"business_name`"$LF",
    "Debug Test Business",
    "--$boundary",
    "Content-Disposition: form-data; name=`"business_type`"$LF",
    "boutique",
    "--$boundary",
    "Content-Disposition: form-data; name=`"business_address`"$LF",
    "123 Business Street",
    "--$boundary",
    "Content-Disposition: form-data; name=`"usage_type`"$LF",
    "TRADER",
    "--$boundary",
    "Content-Disposition: form-data; name=`"signature`"$LF",
    "data:image/png;base64,debug_signature",
    "--$boundary",
    "Content-Disposition: form-data; name=`"accept_terms`"$LF",
    "1",
    "--$boundary",
    "Content-Disposition: form-data; name=`"id_card`"; filename=`"debug_id_card.txt`"",
    "Content-Type: text/plain$LF",
    [System.Text.Encoding]::UTF8.GetString($fileBytes),
    "--$boundary",
    "Content-Disposition: form-data; name=`"cfe_document`"; filename=`"debug_cfe.txt`"",
    "Content-Type: text/plain$LF",
    [System.Text.Encoding]::UTF8.GetString($fileBytes),
    "--$boundary--$LF"
) -join $LF

# Convert to bytes
$body = [System.Text.Encoding]::UTF8.GetBytes($bodyLines)

# Make request
try {
    Write-Host "Creating merchant application with debug logging..." -ForegroundColor Yellow
    $response = Invoke-WebRequest -Uri $uri -Method POST -Body $body -ContentType "multipart/form-data; boundary=$boundary"
    Write-Host "Response Status: $($response.StatusCode)" -ForegroundColor Green
    
    # Parse response
    $jsonResponse = $response.Content | ConvertFrom-Json
    
    if ($jsonResponse.success) {
        $applicationId = $jsonResponse.data.id
        $referenceNumber = $jsonResponse.data.reference_number
        $documentCount = if ($jsonResponse.data.documents) { $jsonResponse.data.documents.count } else { 0 }
        
        Write-Host "`nApplication created successfully!" -ForegroundColor Green
        Write-Host "ID: $applicationId" -ForegroundColor Cyan
        Write-Host "Reference: $referenceNumber" -ForegroundColor Cyan
        Write-Host "Documents saved: $documentCount" -ForegroundColor $(if($documentCount -gt 0) { "Green" } else { "Red" })
        
        Write-Host "`nNow check Laravel logs for debug information..." -ForegroundColor Yellow
        
    } else {
        Write-Host "Application creation failed!" -ForegroundColor Red
        Write-Host $jsonResponse.message -ForegroundColor Red
    }
    
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Error Response: $responseBody" -ForegroundColor Red
    }
}