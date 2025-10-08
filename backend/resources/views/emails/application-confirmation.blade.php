<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmation - Moov Money</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: linear-gradient(135deg, #ff6b35, #f7931e); padding: 20px; text-align: center; color: white; }
        .content { padding: 30px 20px; max-width: 600px; margin: 0 auto; }
        .info-box { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏪 Moov Money</h1>
        <p>Candidature Marchand</p>
    </div>
    
    <div class="content">
        <h2>Bonjour {{ $application->full_name }},</h2>
        
        <p>Nous avons bien reçu votre candidature pour devenir marchand Moov Money.</p>
        
        <div class="info-box">
            <h3>📋 Récapitulatif</h3>
            <p><strong>Référence:</strong> {{ $application->reference_number }}</p>
            <p><strong>Commerce:</strong> {{ $application->business_name }}</p>
            <p><strong>Type:</strong> {{ $application->business_type_label }}</p>
            <p><strong>Date:</strong> {{ $application->submitted_at->format('d/m/Y à H:i') }}</p>
        </div>
        
        <h3>🔄 Prochaines étapes</h3>
        <ol>
            <li>Vérification du dossier (1-2 jours)</li>
            <li>Contact pour validation</li>
            <li>Formation Moov Money</li>
            <li>Activation de votre compte</li>
        </ol>
        
        <p><strong>📞 Besoin d'aide?</strong><br>
        Contactez-nous au +228 99 99 01 11<br>
        Référence: {{ $application->reference_number }}</p>
        
        <p>Cordialement,<br>L'équipe Moov Money</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} Moov Money - Tous droits réservés</p>
    </div>
</body>
</html>