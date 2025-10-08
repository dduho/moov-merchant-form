# Test application creation with debugging to see what happens with documents

$uri = "http://127.0.0.1:8000/api/test-form"  # Use test endpoint first
$filePath = "c:\Users\dduho\Documents\FLOOZ\Projets-Web\moov-merchant-form\test-document.txt"

# Create multipart form data matching frontend structure
$boundary = [System.Guid]::NewGuid().ToString()
$LF = "`r`n"

# Read file content
$fileBytes = [System.IO.File]::ReadAllBytes($filePath)

# Build multipart body matching frontend field names
$bodyLines = @(
    "--$boundary",
    "Content-Disposition: form-data; name=`"first_name`"$LF",
    "Test User",
    "--$boundary",
    "Content-Disposition: form-data; name=`"id_card`"; filename=`"id_card.txt`"",
    "Content-Type: text/plain$LF",
    [System.Text.Encoding]::UTF8.GetString($fileBytes),
    "--$boundary",
    "Content-Disposition: form-data; name=`"anid_card`"; filename=`"anid_card.txt`"",
    "Content-Type: text/plain$LF",
    [System.Text.Encoding]::UTF8.GetString($fileBytes),
    "--$boundary",
    "Content-Disposition: form-data; name=`"cfe_document`"; filename=`"cfe_document.txt`"",
    "Content-Type: text/plain$LF",
    [System.Text.Encoding]::UTF8.GetString($fileBytes),
    "--$boundary--$LF"
) -join $LF

# Convert to bytes
$body = [System.Text.Encoding]::UTF8.GetBytes($bodyLines)

# Make request
try {
    Write-Host "Testing document detection..." -ForegroundColor Yellow
    $response = Invoke-WebRequest -Uri $uri -Method POST -Body $body -ContentType "multipart/form-data; boundary=$boundary"
    Write-Host "Response Status: $($response.StatusCode)" -ForegroundColor Green
    
    $jsonResponse = $response.Content | ConvertFrom-Json
    Write-Host "`nFiles detected:" -ForegroundColor Cyan
    $jsonResponse.has_files.PSObject.Properties | ForEach-Object {
        $color = if ($_.Value) { "Green" } else { "Red" }
        Write-Host "- $($_.Name): $($_.Value)" -ForegroundColor $color
    }
    
    Write-Host "`nTotal files found: $($jsonResponse.total_files_found)" -ForegroundColor $(if($jsonResponse.total_files_found -gt 0) { "Green" } else { "Red" })
    
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Error Response: $responseBody" -ForegroundColor Red
    }
}