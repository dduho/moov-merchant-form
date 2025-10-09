<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidature Approuvée - Moov Money</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 28px;">🎉 Félicitations !</h1>
        <p style="margin: 10px 0 0; font-size: 16px; opacity: 0.9;">Votre candidature a été approuvée</p>
    </div>

    <!-- Main Content -->
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p style="font-size: 18px; margin-bottom: 20px;">
            Bonjour <strong>{{ $application->full_name }}</strong>,
        </p>

        <p style="margin-bottom: 20px;">
            Nous avons le plaisir de vous informer que votre candidature pour devenir partenaire Moov Money 
            <strong>#{{ $application->reference_number }}</strong> a été <span style="color: #28a745; font-weight: bold;">approuvée</span> !
        </p>

        <!-- Application Details -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #FF6B35; margin-top: 0;">Détails de votre candidature :</h3>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li><strong>Numéro de référence :</strong> {{ $application->reference_number }}</li>
                <li><strong>Nom du commerce :</strong> {{ $application->business_name }}</li>
                <li><strong>Date d'approbation :</strong> {{ now()->format('d/m/Y à H:i') }}</li>
                <li><strong>Statut :</strong> <span style="color: #28a745; font-weight: bold;">Approuvée</span></li>
            </ul>
        </div>

        <!-- Next Steps -->
        <div style="background: #e8f4fd; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #005BA4; margin-top: 0;">🚀 Prochaines étapes</h3>
            <p>Notre équipe va vous contacter dans les prochaines 48 heures pour :</p>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Finaliser votre inscription</li>
                <li>Vous fournir votre matériel Moov Money</li>
                <li>Programmer votre formation</li>
                <li>Activer votre compte partenaire</li>
            </ul>
        </div>

        <!-- Important Note -->
        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #856404;">
                <strong>Important :</strong> Veuillez vous assurer que votre téléphone {{ $application->phone }} 
                est accessible. Notre équipe vous contactera sur ce numéro.
            </p>
        </div>

        <p style="margin-top: 30px;">
            Nous sommes ravis de vous accueillir dans la famille Moov Money ! 
            Ensemble, nous allons révolutionner les services financiers mobiles.
        </p>

        <p style="margin-top: 20px; color: #666;">
            Cordialement,<br>
            <strong>L'équipe Moov Money</strong>
        </p>
    </div>

    <!-- Footer -->
    <div style="background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; margin-top: 20px;">
        <p style="color: #666; font-size: 14px; margin: 0;">
            Pour toute question, contactez-nous à 
            <a href="mailto:support@moovmoney.com" style="color: #FF6B35;">support@moovmoney.com</a>
        </p>
        <p style="color: #666; font-size: 12px; margin: 10px 0 0;">
            © {{ date('Y') }} Moov Money. Tous droits réservés.
        </p>
    </div>
</body>
</html>