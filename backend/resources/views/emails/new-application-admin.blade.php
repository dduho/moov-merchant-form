<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nouvelle candidature</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .header { background: #343a40; padding: 20px; color: white; }
        .content { padding: 20px; }
        .info-card { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #ff6b35; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🆕 Nouvelle candidature</h1>
        <p>Référence: {{ $application->reference_number }}</p>
    </div>
    
    <div class="content">
        <div class="info-card">
            <h3>Candidat</h3>
            <p>{{ $application->full_name }}</p>
            <p>📞 {{ $application->phone }}</p>
            @if($application->email)
            <p>✉️ {{ $application->email }}</p>
            @endif
        </div>
        
        <div class="info-card">
            <h3>Commerce</h3>
            <p>{{ $application->business_name }}</p>
            <p>🏪 {{ $application->business_type_label }}</p>
        </div>
        
        <div class="info-card">
            <h3>Documents</h3>
            <p>{{ $application->documents->count() }} document(s)</p>
            <p>@if($application->hasRequiredDocuments()) ✅ Complet @else ⚠️ Incomplet @endif</p>
        </div>
        
        <p><small>Soumis le {{ $application->submitted_at->format('d/m/Y à H:i') }}</small></p>
    </div>
</body>
</html>