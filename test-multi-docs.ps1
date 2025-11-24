# Multi-document test script

$uri = "http://127.0.0.1:8000/api/test-form"
$filePath = "c:\Users\dduho\Documents\FLOOZ\Projets-Web\moov-merchant-form\test-document.txt"

# Create multipart form data with multiple documents
$boundary = [System.Guid]::NewGuid().ToString()
$LF = "`r`n"

# Read file content
$fileBytes = [System.IO.File]::ReadAllBytes($filePath)
$fileName = [System.IO.Path]::GetFileName($filePath)

# Build multipart body with multiple document fields
$bodyLines = @(
    "--$boundary",
    "Content-Disposition: form-data; name=`"first_name`"$LF",
    "Test User",
    "--$boundary",
    "Content-Disposition: form-data; name=`"id_card`"; filename=`"id_card_$fileName`"",
    "Content-Type: text/plain$LF",
    [System.Text.Encoding]::UTF8.GetString($fileBytes),
    "--$boundary",
    "Content-Disposition: form-data; name=`"anid_card`"; filename=`"anid_card_$fileName`"",
    "Content-Type: text/plain$LF", 
    [System.Text.Encoding]::UTF8.GetString($fileBytes),
    "--$boundary",
    "Content-Disposition: form-data; name=`"cfe_document`"; filename=`"cfe_document_$fileName`"",
    "Content-Type: text/plain$LF",
    [System.Text.Encoding]::UTF8.GetString($fileBytes),
    "--$boundary--$LF"
) -join $LF

# Convert to bytes
$body = [System.Text.Encoding]::UTF8.GetBytes($bodyLines)

# Make request
try {
    $response = Invoke-WebRequest -Uri $uri -Method POST -Body $body -ContentType "multipart/form-data; boundary=$boundary"
    Write-Host "Response Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Response Content:" -ForegroundColor Yellow
    $jsonResponse = $response.Content | ConvertFrom-Json
    $jsonResponse | ConvertTo-Json -Depth 4
    
    Write-Host "`nSUMMARY:" -ForegroundColor Cyan
    Write-Host "Total files detected: $($jsonResponse.total_files_found)" -ForegroundColor $(if($jsonResponse.total_files_found -gt 0) { "Green" } else { "Red" })
    
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Error Response: $responseBody" -ForegroundColor Red
    }
}