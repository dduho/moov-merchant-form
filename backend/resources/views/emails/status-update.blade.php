<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mise à jour</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h2>Mise à jour de votre candidature</h2>
    
    <p>Bonjour {{ $application->full_name }},</p>
    
    <p>Statut de votre candidature: <strong>{{ $application->status_label }}</strong></p>
    
    <p>Référence: {{ $application->reference_number }}</p>
    
    @if($application->admin_notes)
    <p><strong>Note:</strong> {{ $application->admin_notes }}</p>
    @endif
    
    <p>Cordialement,<br>L'équipe Moov Money</p>
</body>
</html>