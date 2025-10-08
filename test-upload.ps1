# Test script to check document upload functionality

$uri = "http://127.0.0.1:8000/api/test-form"
$filePath = "c:\Users\dduho\Documents\FLOOZ\Projets-Web\moov-merchant-form\test-document.txt"

# Create multipart form data
$boundary = [System.Guid]::NewGuid().ToString()
$LF = "`r`n"

# Read file content
$fileBytes = [System.IO.File]::ReadAllBytes($filePath)
$fileName = [System.IO.Path]::GetFileName($filePath)

# Build multipart body
$bodyLines = (
    "--$boundary",
    "Content-Disposition: form-data; name=`"id_card`"; filename=`"$fileName`"",
    "Content-Type: text/plain$LF",
    [System.Text.Encoding]::UTF8.GetString($fileBytes),
    "--$boundary",
    "Content-Disposition: form-data; name=`"first_name`"$LF",
    "Test User",
    "--$boundary--$LF"
) -join $LF

# Convert to bytes
$body = [System.Text.Encoding]::UTF8.GetBytes($bodyLines)

# Make request
try {
    $response = Invoke-WebRequest -Uri $uri -Method POST -Body $body -ContentType "multipart/form-data; boundary=$boundary"
    Write-Host "Response Status: $($response.StatusCode)"
    Write-Host "Response Content:"
    $response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 3
} catch {
    Write-Host "Error: $($_.Exception.Message)"
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Error Response: $responseBody"
    }
}