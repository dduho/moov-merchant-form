<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidature à Réviser - Moov Money</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 28px;">📝 Candidature à réviser</h1>
        <p style="margin: 10px 0 0; font-size: 16px; opacity: 0.9;">Des modifications sont nécessaires</p>
    </div>

    <!-- Main Content -->
    <div style="background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 0 0 10px 10px;">
        <p style="font-size: 18px; margin-bottom: 20px;">
            Bonjour <strong>{{ $application->full_name }}</strong>,
        </p>

        <p style="margin-bottom: 20px;">
            Nous vous remercions pour votre candidature pour devenir partenaire Moov Money 
            <strong>#{{ $application->reference_number }}</strong>.
        </p>

        <p style="margin-bottom: 20px;">
            Après examen de votre dossier, nous avons identifié quelques éléments qui nécessitent 
            des <span style="color: #dc3545; font-weight: bold;">modifications ou des clarifications</span> 
            avant que nous puissions approuver votre candidature.
        </p>

        <!-- Application Details -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #FF6B35; margin-top: 0;">Détails de votre candidature :</h3>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li><strong>Numéro de référence :</strong> {{ $application->reference_number }}</li>
                <li><strong>Nom du commerce :</strong> {{ $application->business_name }}</li>
                <li><strong>Date de révision :</strong> {{ now()->format('d/m/Y à H:i') }}</li>
                <li><strong>Statut :</strong> <span style="color: #dc3545; font-weight: bold;">À réviser</span></li>
            </ul>
        </div>

        @if($reason)
        <!-- Reason for Rejection -->
        <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0;">
            <h4 style="color: #721c24; margin-top: 0;">Raison de la révision :</h4>
            <p style="margin: 0; color: #721c24;">{{ $reason }}</p>
        </div>
        @endif

        <!-- Action Required -->
        <div style="background: #cce7ff; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #005BA4; margin-top: 0;">🔄 Action requise</h3>
            <p>Pour que nous puissions traiter votre candidature :</p>
            <ol style="margin: 10px 0; padding-left: 20px;">
                <li>Consultez les commentaires de notre équipe</li>
                <li>Apportez les modifications nécessaires à votre candidature</li>
                <li>Téléchargez les documents manquants ou corrigés</li>
                <li>Resoumettez votre candidature</li>
            </ol>
        </div>

        @if($application->user_id)
        <!-- Login Button for Registered Users -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/applications/{{ $application->id }}/edit" 
               style="display: inline-block; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); color: white; text-decoration: none; padding: 12px 30px; border-radius: 25px; font-weight: bold; transition: all 0.3s;">
                🔧 Modifier ma candidature
            </a>
        </div>
        @endif

        <!-- Important Note -->
        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #856404;">
                <strong>Important :</strong> Vous avez 30 jours pour apporter les modifications nécessaires. 
                Passé ce délai, votre candidature sera automatiquement archivée.
            </p>
        </div>

        <p style="margin-top: 30px;">
            Nous restons à votre disposition pour vous accompagner dans ce processus. 
            N'hésitez pas à nous contacter si vous avez des questions.
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