<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification - {{ $status === 'approved' ? 'Candidature Approuvée' : 'Candidature à Réviser' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f8fafc;
            color: #374151;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        .content {
            padding: 2rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .application-info {
            background-color: #f9fafb;
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            margin: 1rem 0;
        }
        .reason-box {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
        }
        .reason-box h4 {
            margin: 0 0 0.5rem 0;
            color: #92400e;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 600;
            margin: 1rem 0;
        }
        .button:hover {
            background-color: #2563eb;
        }
        .info-box {
            background-color: #eff6ff;
            border: 1px solid #3b82f6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
        }
        .info-box h4 {
            margin: 0 0 0.5rem 0;
            color: #1e40af;
        }
        .footer {
            background-color: #f9fafb;
            padding: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $status === 'approved' ? '🎉 Candidature Approuvée !' : '📝 Candidature à Réviser' }}</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="status-badge {{ $status === 'approved' ? 'status-approved' : 'status-rejected' }}">
                {{ $status === 'approved' ? 'APPROUVÉE' : 'À RÉVISER' }}
            </div>

            <p><strong>Bonjour {{ $application->full_name }},</strong></p>

            @if($status === 'approved')
                <p>Excellente nouvelle ! Votre candidature pour devenir marchand Moov Money a été <strong>approuvée</strong> par notre équipe.</p>
                
                <p>Félicitations ! Vous pouvez maintenant commencer à offrir les services Moov Money à vos clients.</p>
            @else
                <p>Votre candidature pour devenir marchand Moov Money nécessite quelques ajustements avant d'être approuvée.</p>
                
                @if($reason)
                <div class="reason-box">
                    <h4>📋 Détails des modifications requises :</h4>
                    <p>{{ $reason }}</p>
                </div>
                @endif
                
                <p>Pour modifier votre candidature, nous vous invitons à nous contacter ou à consulter votre candidature via le lien ci-dessous.</p>
            @endif

            <!-- Application Info -->
            <div class="application-info">
                <h4>📄 Informations sur votre candidature :</h4>
                <ul style="margin: 0.5rem 0; padding-left: 1.2rem;">
                    <li><strong>Numéro de référence :</strong> {{ $application->reference_number }}</li>
                    <li><strong>Nom commercial :</strong> {{ $application->business_name }}</li>
                    <li><strong>Email :</strong> {{ $application->email }}</li>
                    <li><strong>Téléphone :</strong> {{ $application->phone }}</li>
                    <li><strong>Date de soumission :</strong> {{ $application->created_at->format('d/m/Y à H:i') }}</li>
                    <li><strong>Statut actuel :</strong> 
                        @if($status === 'approved')
                            <span style="color: #065f46; font-weight: 600;">Approuvée ✅</span>
                        @else
                            <span style="color: #991b1b; font-weight: 600;">À réviser 📝</span>
                        @endif
                    </li>
                </ul>
            </div>

            @if($status === 'approved')
                <p><strong>Prochaines étapes :</strong></p>
                <ol style="padding-left: 1.2rem;">
                    <li>Vous recevrez prochainement vos identifiants et matériels Moov Money</li>
                    <li>Un représentant vous contactera pour finaliser votre intégration</li>
                    <li>Vous pourrez commencer vos activités de marchand</li>
                </ol>
            @else
                <div class="info-box">
                    <h4>💡 Comment modifier votre candidature ?</h4>
                    <p>Vous pouvez consulter l'état de votre candidature en utilisant votre numéro de référence :</p>
                    <p><strong>{{ $application->reference_number }}</strong></p>
                    <p>Ou contactez-nous directement pour obtenir de l'aide.</p>
                </div>
                
                <a href="{{ config('app.frontend_url') }}/applications/reference/{{ $application->reference_number }}" class="button">
                    🔍 Consulter ma candidature
                </a>
            @endif

            <!-- Contact Info -->
            <div class="application-info">
                <h4>📞 Besoin d'aide ?</h4>
                <p>Notre équipe est disponible pour vous accompagner :</p>
                <ul style="margin: 0.5rem 0; padding-left: 1.2rem;">
                    <li><strong>Email :</strong> <a href="mailto:support@moovmoney.com">support@moovmoney.com</a></li>
                    <li><strong>Téléphone :</strong> +225 XX XX XX XX</li>
                    <li><strong>Horaires :</strong> Lundi - Vendredi, 8h00 - 18h00</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>Moov Money</strong><br>
                Service Marchand<br>
                <a href="mailto:support@moovmoney.com">support@moovmoney.com</a>
            </p>
            <p style="margin-top: 1rem; font-size: 0.75rem;">
                Cet email a été envoyé automatiquement. Merci de ne pas répondre directement à ce message.
            </p>
        </div>
    </div>
</body>
</html>