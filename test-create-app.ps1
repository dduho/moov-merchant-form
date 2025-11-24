# Test actual merchant application creation with documents

$uri = "http://127.0.0.1:8000/api/merchant-applications"
$filePath = "c:\Users\dduho\Documents\FLOOZ\Projets-Web\moov-merchant-form\test-document.txt"

# Create multipart form data
$boundary = [System.Guid]::NewGuid().ToString()
$LF = "`r`n"

# Read file content
$fileBytes = [System.IO.File]::ReadAllBytes($filePath)
$fileName = [System.IO.Path]::GetFileName($filePath)

# Build form data with required fields and documents
$bodyLines = @(
    "--$boundary",
    "Content-Disposition: form-data; name=`"last_name`"$LF",
    "TestLastName",
    "--$boundary",
    "Content-Disposition: form-data; name=`"first_name`"$LF", 
    "TestFirstName",
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
    "Test Business",
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
    "data:image/png;base64,test_signature_data",
    "--$boundary",
    "Content-Disposition: form-data; name=`"accept_terms`"$LF",
    "1",
    "--$boundary",
    "Content-Disposition: form-data; name=`"id_card`"; filename=`"test_id_card.txt`"",
    "Content-Type: text/plain$LF",
    [System.Text.Encoding]::UTF8.GetString($fileBytes),
    "--$boundary",
    "Content-Disposition: form-data; name=`"cfe_document`"; filename=`"test_cfe.txt`"",
    "Content-Type: text/plain$LF",
    [System.Text.Encoding]::UTF8.GetString($fileBytes),
    "--$boundary--$LF"
) -join $LF

# Convert to bytes
$body = [System.Text.Encoding]::UTF8.GetBytes($bodyLines)

# Make request
try {
    Write-Host "Creating merchant application with documents..." -ForegroundColor Yellow
    $response = Invoke-WebRequest -Uri $uri -Method POST -Body $body -ContentType "multipart/form-data; boundary=$boundary"
    Write-Host "Response Status: $($response.StatusCode)" -ForegroundColor Green
    
    # Parse response
    $jsonResponse = $response.Content | ConvertFrom-Json
    
    if ($jsonResponse.success) {
        $applicationId = $jsonResponse.data.id
        $referenceNumber = $jsonResponse.data.reference_number
        $documentCount = $jsonResponse.data.documents.count
        
        Write-Host "`nApplication created successfully!" -ForegroundColor Green
        Write-Host "ID: $applicationId" -ForegroundColor Cyan
        Write-Host "Reference: $referenceNumber" -ForegroundColor Cyan  
        Write-Host "Documents saved: $documentCount" -ForegroundColor $(if($documentCount -gt 0) { "Green" } else { "Red" })
        
        if ($documentCount -gt 0) {
            Write-Host "`nDocument details:" -ForegroundColor Yellow
            foreach ($doc in $jsonResponse.data.documents) {
                Write-Host "- $($doc.document_type): $($doc.original_name)" -ForegroundColor White
            }
        }
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