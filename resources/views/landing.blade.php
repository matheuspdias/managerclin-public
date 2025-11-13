<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ManagerClin - O sistema de gestão completa para sua clínica.</title>
    <meta name="description" content="Sistema completo para gestão de clínicas médicas com agendamento, prontuário eletrônico, IA integrada, telemedicina e muito mais. Teste grátis por 14 dias!">
    <meta name="keywords" content="sistema clinica medica, prontuario eletronico, agendamento medico, telemedicina, IA medica">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #a5b4fc;
            --secondary: #06b6d4;
            --secondary-dark: #0891b2;
            --accent: #f59e0b;
            --accent-dark: #d97706;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
            --light: #f8fafc;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;

            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --gradient-hero: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);

            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--gray-800);
            line-height: 1.7;
            background-color: var(--gray-50);
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Utilities */
        .text-gradient {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* Header */
        header {
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 16px 0;
        }

        header.scrolled {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--gray-200);
            box-shadow: var(--shadow-lg);
            padding: 12px 0;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo-icon {
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            transition: all 0.3s ease;
        }

        .logo:hover .logo-icon {
            transform: rotate(5deg) scale(1.1);
        }

        /* Logo styles when header is scrolled */
        header.scrolled .logo {
            color: var(--primary);
            text-shadow: none;
        }

        header.scrolled .logo-icon {
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 32px;
        }

        nav ul li a {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            font-size: 16px;
            transition: all 0.3s ease;
            position: relative;
            padding: 8px 0;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        /* Navigation styles when header is scrolled */
        header.scrolled nav ul li a {
            color: var(--gray-700);
            text-shadow: none;
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background: var(--gradient-primary);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        nav ul li a:hover::after {
            width: 100%;
        }

        nav ul li a:hover {
            color: var(--primary);
        }

        .header-buttons {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Header button styles when on hero */
        header:not(.scrolled) .btn-outline {
            color: white;
            border-color: rgba(255, 255, 255, 0.8);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        header:not(.scrolled) .btn-outline:hover {
            background: white;
            color: var(--primary);
            border-color: white;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: var(--gradient-hero);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
            position: relative;
            z-index: 2;
            padding: 120px 0 80px;
        }

        .hero-text h1 {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(3rem, 5vw, 4.5rem);
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 24px;
            color: white;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
        }

        .hero-text h1 .highlight-text {
            color: white;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: none;
            position: relative;
            display: inline-block;
        }

        .hero-text h1 .highlight-text::after {
            content: attr(data-text);
            position: absolute;
            left: 0;
            top: 0;
            z-index: -1;
            -webkit-text-fill-color: rgba(255, 255, 255, 0.2);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-text .subtitle {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .hero-image {
            position: relative;
        }

        .hero-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: var(--shadow-2xl);
            transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
            transition: transform 0.3s ease;
        }

        .hero-image:hover img {
            transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
        }

        /* Floating elements */
        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        /* Stats Section */
        .stats {
            background: white;
            padding: 80px 0;
            margin-top: -40px;
            position: relative;
            z-index: 3;
        }

        .stats-container {
            background: white;
            border-radius: 24px;
            padding: 60px;
            box-shadow: var(--shadow-2xl);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            text-align: center;
        }

        .stat-item h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 48px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .stat-item p {
            color: var(--gray-600);
            font-weight: 500;
        }

        /* Features Section */
        .features {
            padding: 120px 0;
            background: var(--gray-50);
        }

        .section-title {
            text-align: center;
            margin-bottom: 80px;
        }

        .section-title h2 {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 20px;
            color: var(--gray-900);
        }

        .section-title .subtitle {
            font-size: 20px;
            color: var(--gray-600);
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: var(--shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--gray-100);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 32px;
            color: white;
        }

        .feature-title {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--gray-900);
        }

        .feature-description {
            color: var(--gray-600);
            line-height: 1.6;
        }

        /* AI Section */
        .ai-section {
            padding: 120px 0;
            background: var(--gray-900);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .ai-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="ai-gradient"><stop offset="0%" stop-color="rgba(99,102,241,0.1)"/><stop offset="100%" stop-color="transparent"/></radialGradient></defs><circle cx="200" cy="200" r="300" fill="url(%23ai-gradient)"/><circle cx="800" cy="800" r="400" fill="url(%23ai-gradient)"/></svg>');
        }

        .ai-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .ai-text h2 {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 24px;
            background: linear-gradient(135deg, #fff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .ai-features {
            list-style: none;
            margin: 32px 0;
        }

        .ai-features li {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .ai-features i {
            color: var(--primary-light);
            font-size: 20px;
            min-width: 24px;
        }

        .ai-visual {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .ai-visual img {
            width: 100%;
            border-radius: 16px;
            box-shadow: var(--shadow-xl);
            transition: transform 0.3s ease;
        }

        .ai-visual img:hover {
            transform: scale(1.05);
        }

        /* Pricing Section */
        .pricing {
            padding: 120px 0;
            background: white;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
            margin-top: 80px;
        }

        .pricing-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid var(--gray-100);
            position: relative;
        }

        .pricing-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-2xl);
        }

        .pricing-card.popular {
            border: 2px solid var(--primary);
            transform: scale(1.05);
        }

        .pricing-card.popular::before {
            content: "✨ Mais Popular";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: var(--gradient-primary);
            color: white;
            padding: 12px;
            text-align: center;
            font-weight: 700;
            font-size: 14px;
        }

        .pricing-header {
            padding: 40px 40px 20px;
            text-align: center;
            background: var(--gray-50);
        }

        .pricing-card.popular .pricing-header {
            padding-top: 60px;
        }

        .pricing-name {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--gray-900);
        }

        .price {
            font-family: 'Poppins', sans-serif;
            font-size: 48px;
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .price-period {
            color: var(--gray-500);
            font-size: 16px;
            font-weight: 500;
        }

        .pricing-description {
            color: var(--gray-600);
            margin-top: 16px;
            line-height: 1.5;
        }

        .pricing-features {
            padding: 40px;
        }

        .pricing-features ul {
            list-style: none;
        }

        .pricing-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding: 8px 0;
        }

        .pricing-features i {
            color: var(--success);
            font-size: 18px;
            min-width: 20px;
        }

        .additional-info {
            background: var(--gray-50);
            padding: 20px 40px;
            text-align: center;
            border-top: 1px solid var(--gray-200);
            color: var(--gray-600);
            font-size: 14px;
        }

        .pricing-cta {
            padding: 40px;
            text-align: center;
        }

        /* Credits Section */
        .credits {
            padding: 80px 0;
            background: var(--gray-50);
        }

        .credits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
            margin-top: 60px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .credit-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .credit-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: var(--shadow-xl);
        }

        .credit-title {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 16px;
        }

        .credit-price {
            font-family: 'Poppins', sans-serif;
            font-size: 36px;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 16px;
        }

        /* Testimonials */
        .testimonials {
            padding: 120px 0;
            background: white;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 40px;
            margin-top: 80px;
        }

        .testimonial-card {
            background: var(--gray-50);
            padding: 40px;
            border-radius: 24px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid var(--gray-200);
        }

        .testimonial-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 30px;
            font-size: 80px;
            color: var(--primary);
            font-family: serif;
            opacity: 0.3;
        }

        .testimonial-text {
            font-style: italic;
            margin-bottom: 32px;
            font-size: 18px;
            line-height: 1.6;
            color: var(--gray-700);
            position: relative;
            z-index: 2;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
        }

        .author-name {
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .author-role {
            color: var(--gray-600);
        }

        /* CTA Section */
        .cta {
            padding: 120px 0;
            background: var(--gradient-hero);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="cta-pattern" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23cta-pattern)"/></svg>');
        }

        .cta-content {
            position: relative;
            z-index: 2;
        }

        .cta h2 {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 900;
            margin-bottom: 24px;
        }

        .cta p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Footer */
        footer {
            background: var(--gray-900);
            color: white;
            padding: 80px 0 40px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 60px;
            margin-bottom: 60px;
        }

        .footer-column h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: var(--gray-400);
            text-decoration: none;
            transition: color 0.3s ease;
            display: block;
            padding: 4px 0;
        }

        .footer-links a:hover {
            color: white;
        }

        .social-links {
            display: flex;
            gap: 16px;
            margin-top: 24px;
        }

        .social-links a {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--gray-800);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }

        .copyright {
            text-align: center;
            padding-top: 40px;
            border-top: 1px solid var(--gray-800);
            color: var(--gray-400);
        }

        /* Mobile Navigation */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: white;
            cursor: pointer;
            padding: 8px;
            z-index: 1001;
        }

        header.scrolled .mobile-menu-toggle {
            color: var(--gray-700);
        }

        .mobile-sidebar {
            position: fixed;
            top: 0;
            right: -100%;
            width: 280px;
            height: 100vh;
            background: white;
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 1002;
            transition: right 0.3s ease;
            overflow-y: auto;
        }

        .mobile-sidebar.active {
            right: 0;
        }

        .mobile-sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1001;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .mobile-sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .mobile-sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid var(--gray-200);
        }

        .mobile-sidebar-close {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--gray-700);
            cursor: pointer;
            padding: 8px;
        }

        .mobile-sidebar-nav {
            padding: 20px 0;
        }

        .mobile-sidebar-nav ul {
            list-style: none;
        }

        .mobile-sidebar-nav li {
            margin: 0;
        }

        .mobile-sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .mobile-sidebar-nav a:hover {
            background: var(--gray-50);
            border-left-color: var(--primary);
            color: var(--primary);
        }

        .mobile-sidebar-actions {
            padding: 20px;
            border-top: 1px solid var(--gray-200);
        }

        .mobile-sidebar-actions .btn {
            width: 100%;
            margin-bottom: 12px;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {

            .hero-content,
            .ai-content {
                grid-template-columns: 1fr;
                gap: 60px;
                text-align: center;
            }

            .demo-section div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
                gap: 40px !important;
                text-align: center;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 32px;
                padding: 40px;
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 40px;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 20px;
            }

            .mobile-menu-toggle {
                display: block;
            }

            nav {
                display: none;
            }

            .header-buttons {
                gap: 8px;
            }

            .header-buttons .btn {
                padding: 8px 16px;
                font-size: 13px;
                white-space: nowrap;
            }

            .header-buttons .btn i {
                display: none;
            }

            .hero-content {
                padding-top: 140px;
            }
        }

        @media (max-width: 480px) {
            .logo {
                font-size: 20px;
            }

            .header-buttons .btn {
                padding: 6px 12px;
                font-size: 11px;
            }

            .logo-icon {
                width: 28px;
                height: 28px;
            }

            .hero-content {
                padding-top: 120px;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .hero-text h1 {
                font-size: 2.5rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
                gap: 24px;
                padding: 32px 24px;
            }

            .features-grid,
            .pricing-grid,
            .testimonials-grid,
            .credits-grid {
                grid-template-columns: 1fr;
            }

            .pricing-card.popular {
                transform: none;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }

            .ai-visual {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        /* Scroll animations */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Custom Plan Notice Styles */
        .custom-plan-notice {
            text-align: center;
            margin-top: 60px;
            padding: 32px;
            background: var(--gray-50);
            border-radius: 16px;
            border: 2px dashed var(--gray-300);
        }

        .custom-plan-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px 40px;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            max-width: 900px;
            margin: 0 auto;
        }

        .custom-plan-icon {
            font-size: 32px;
            color: var(--primary);
            flex-shrink: 0;
        }

        .custom-plan-content {
            text-align: left;
            flex: 1;
        }

        .custom-plan-title {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .custom-plan-text {
            color: var(--gray-600);
            margin: 0;
            font-size: 16px;
        }

        .custom-plan-btn {
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* Mobile Responsive for Custom Plan */
        @media (max-width: 768px) {
            .custom-plan-notice {
                margin-top: 40px;
                padding: 20px;
            }

            .custom-plan-card {
                flex-direction: column;
                text-align: center;
                padding: 24px 20px;
                gap: 20px;
            }

            .custom-plan-icon {
                font-size: 48px;
            }

            .custom-plan-content {
                text-align: center;
            }

            .custom-plan-title {
                font-size: 18px;
                margin-bottom: 8px;
            }

            .custom-plan-text {
                font-size: 14px;
            }

            .custom-plan-btn {
                width: 100%;
                white-space: normal;
            }
        }

        @media (max-width: 480px) {
            .custom-plan-title {
                font-size: 16px;
            }

            .custom-plan-text {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header id="header">
        <div class="container header-container">
            <a href="#" class="logo">
                <svg width="36" height="36" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="logo-icon">
                    <!-- Background circle with gradient -->
                    <circle cx="20" cy="20" r="20" fill="url(#logoGradient)" />

                    <!-- Medical cross -->
                    <rect x="17" y="10" width="6" height="20" rx="3" fill="white" />
                    <rect x="10" y="17" width="20" height="6" rx="3" fill="white" />

                    <!-- Heart pulse line -->
                    <path d="M8 20L12 16L16 24L20 12L24 28L28 16L32 20" stroke="rgba(255,255,255,0.8)" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />

                    <!-- Small medical symbols -->
                    <circle cx="14" cy="14" r="1.5" fill="url(#logoGradient)" />
                    <circle cx="26" cy="14" r="1.5" fill="url(#logoGradient)" />
                    <circle cx="14" cy="26" r="1.5" fill="url(#logoGradient)" />
                    <circle cx="26" cy="26" r="1.5" fill="url(#logoGradient)" />

                    <defs>
                        <linearGradient id="logoGradient" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#667eea" />
                            <stop offset="0.5" stop-color="#764ba2" />
                            <stop offset="1" stop-color="#f093fb" />
                        </linearGradient>
                    </defs>
                </svg>
                ManagerClin
            </a>

            <nav id="nav">
                <ul>
                    <li><a href="#features">Recursos</a></li>
                    <li><a href="#ai">IA Médica</a></li>
                    <li><a href="#pricing">Planos</a></li>
                    <li><a href="#testimonials">Depoimentos</a></li>
                    <li><a href="#contact">Contato</a></li>
                </ul>
            </nav>

            <div class="header-buttons">
                <a href="{{ route('login') }}" class="btn btn-outline">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar
                </a>
                <a href="{{ route('register') }}" class="btn btn-primary">
                    <i class="fas fa-rocket"></i>
                    Teste Grátis
                </a>
            </div>

            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Mobile Sidebar -->
    <div class="mobile-sidebar-overlay" id="mobileSidebarOverlay"></div>
    <div class="mobile-sidebar" id="mobileSidebar">
        <div class="mobile-sidebar-header">
            <a href="#" class="logo" style="color: var(--primary); text-shadow: none;">
                <svg width="32" height="32" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="20" fill="url(#mobileLogoGradient)" />
                    <rect x="17" y="10" width="6" height="20" rx="3" fill="white" />
                    <rect x="10" y="17" width="20" height="6" rx="3" fill="white" />
                    <defs>
                        <linearGradient id="mobileLogoGradient" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#667eea" />
                            <stop offset="0.5" stop-color="#764ba2" />
                            <stop offset="1" stop-color="#f093fb" />
                        </linearGradient>
                    </defs>
                </svg>
                ManagerClin
            </a>
            <button class="mobile-sidebar-close" id="mobileSidebarClose">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="mobile-sidebar-nav">
            <ul>
                <li><a href="#features"><i class="fas fa-star"></i> Recursos</a></li>
                <li><a href="#ai"><i class="fas fa-brain"></i> IA Médica</a></li>
                <li><a href="#pricing"><i class="fas fa-tag"></i> Planos</a></li>
                <li><a href="#testimonials"><i class="fas fa-comments"></i> Depoimentos</a></li>
                <li><a href="#contact"><i class="fas fa-envelope"></i> Contato</a></li>
            </ul>
        </div>

        <div class="mobile-sidebar-actions">
            <a href="{{ route('login') }}" class="btn btn-outline">
                <i class="fas fa-sign-in-alt"></i>
                Entrar
            </a>
            <a href="{{ route('register') }}" class="btn btn-primary">
                <i class="fas fa-rocket"></i>
                Teste Grátis
            </a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-content">
            <div class="hero-text" data-aos="fade-right" data-aos-duration="1000">
                <h1>Revolução na Gestão de <span class="highlight-text" data-text="Clínicas Médicas">Clínicas Médicas</span></h1>
                <p class="subtitle">Sistema completo com IA integrada para agendamentos, prontuários eletrônicos, telemedicina e gestão financeira. Automatize sua clínica e foque no que realmente importa: seus pacientes.</p>

                <div class="hero-buttons">
                    <a href="#pricing" class="btn btn-glass">
                        <i class="fas fa-play"></i>
                        Começar Agora
                    </a>
                    <a href="#features" class="btn btn-glass">
                        <i class="fas fa-eye"></i>
                        Ver Demonstração
                    </a>
                </div>
            </div>

            <div class="hero-image" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                <img src="/images/medico_dashboard.png" alt="Dashboard do ManagerClin - Sistema de Gestão de Clínicas">
            </div>
        </div>

        <!-- Floating elements -->
        <div class="floating-element" style="top: 20%; left: 10%; animation-delay: 0s;">
            <i class="fas fa-stethoscope" style="font-size: 60px; color: rgba(255,255,255,0.1);"></i>
        </div>
        <div class="floating-element" style="top: 60%; right: 10%; animation-delay: 2s;">
            <i class="fas fa-heartbeat" style="font-size: 80px; color: rgba(255,255,255,0.1);"></i>
        </div>
        <div class="floating-element" style="bottom: 20%; left: 20%; animation-delay: 4s;">
            <i class="fas fa-brain" style="font-size: 50px; color: rgba(255,255,255,0.1);"></i>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-container" data-aos="fade-up" data-aos-duration="800">
                <div class="stat-item">
                    <h3 class="counter" data-count="1000">0</h3>
                    <p>Clínicas Atendidas</p>
                </div>
                <div class="stat-item">
                    <h3 class="counter" data-count="50000">0</h3>
                    <p>Consultas Agendadas</p>
                </div>
                <div class="stat-item">
                    <h3 class="counter" data-count="98">0</h3>
                    <p>% de Satisfação</p>
                </div>
                <div class="stat-item">
                    <h3 class="counter" data-count="24">0</h3>
                    <p>Suporte 24/7</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Recursos Avançados para<br><span class="text-gradient">Sua Clínica</span></h2>
                <p class="subtitle">Tecnologia de ponta para modernizar completamente a gestão do seu consultório médico</p>
            </div>

            <div class="features-grid">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="feature-title">Agendamento Inteligente</h3>
                    <p class="feature-description">Sistema avançado de agendamento com confirmações automáticas via WhatsApp, controle de disponibilidade e redução de faltas de até 80%.</p>
                </div>

                <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-file-medical-alt"></i>
                    </div>
                    <h3 class="feature-title">Prontuário Eletrônico</h3>
                    <p class="feature-description">Prontuários digitais completos com histórico médico, receitas eletrônicas, laudos e assinatura digital certificada.</p>
                </div>

                <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="feature-title">Assistente de IA</h3>
                    <p class="feature-description">Inteligência artificial médica para auxílio em diagnósticos, sugestões de tratamentos e otimização de processos clínicos.</p>
                </div>

                <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3 class="feature-title">Controle de Estoque</h3>
                    <p class="feature-description">Gestão completa de produtos médicos com controle de validade, alertas automáticos de estoque baixo e relatórios detalhados de movimentação.</p>
                </div>

                <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Gestão Financeira</h3>
                    <p class="feature-description">Controle completo de receitas, despesas, cobrança automática e relatórios financeiros detalhados.</p>
                </div>

                <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">Segurança LGPD</h3>
                    <p class="feature-description">Máxima segurança dos dados com criptografia avançada, backup automático e conformidade total com a LGPD.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section class="demo-section" style="padding: 120px 0; background: white;">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Veja o Sistema em <span class="text-gradient">Funcionamento</span></h2>
                <p class="subtitle">Conheça as principais funcionalidades através de capturas reais do sistema</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; margin-top: 80px; align-items: center;">
                <!-- Agendamento Inteligente -->
                <div data-aos="fade-right" data-aos-delay="100">
                    <div style="position: relative;">
                        <img src="/images/agendamentos2.png" alt="Calendário de Agendamentos" style="width: 100%; border-radius: 16px; box-shadow: var(--shadow-xl); transition: transform 0.3s ease;">
                    </div>
                </div>
                <div data-aos="fade-left" data-aos-delay="200">
                    <h3 style="font-family: 'Poppins', sans-serif; font-size: 2rem; font-weight: 700; margin-bottom: 24px; color: var(--gray-900);">
                        <i class="fas fa-calendar-alt" style="color: var(--primary); margin-right: 12px;"></i>
                        Agendamento Inteligente
                    </h3>
                    <p style="color: var(--gray-600); font-size: 18px; line-height: 1.6; margin-bottom: 32px;">
                        Sistema avançado de agendamento com calendário interativo, múltiplas visualizações e controle completo de horários. Gerencie sua agenda com facilidade.
                    </p>
                    <ul style="list-style: none; margin-bottom: 32px;">
                        <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; color: var(--gray-700);">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            Calendário interativo com múltiplas visualizações
                        </li>
                        <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; color: var(--gray-700);">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            Confirmações automáticas via WhatsApp
                        </li>
                        <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; color: var(--gray-700);">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            Controle de disponibilidade por profissional
                        </li>
                    </ul>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; margin-top: 80px; align-items: center;">
                <!-- Controle de Estoque -->
                <div data-aos="fade-right" data-aos-delay="100">
                    <h3 style="font-family: 'Poppins', sans-serif; font-size: 2rem; font-weight: 700; margin-bottom: 24px; color: var(--gray-900);">
                        <i class="fas fa-boxes" style="color: var(--primary); margin-right: 12px;"></i>
                        Controle de Estoque
                    </h3>
                    <p style="color: var(--gray-600); font-size: 18px; line-height: 1.6; margin-bottom: 32px;">
                        Monitore produtos, validades e níveis de estoque em tempo real. Receba alertas automáticos para produtos com estoque baixo ou próximos ao vencimento.
                    </p>
                    <ul style="list-style: none; margin-bottom: 32px;">
                        <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; color: var(--gray-700);">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            Controle de validade e lotes
                        </li>
                        <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; color: var(--gray-700);">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            Alertas automáticos de estoque baixo
                        </li>
                        <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; color: var(--gray-700);">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            Relatórios de movimentação
                        </li>
                    </ul>
                </div>
                <div data-aos="fade-left" data-aos-delay="200">
                    <div style="position: relative;">
                        <img src="/images/dashboard_estoque.png" alt="Dashboard de Controle de Estoque" style="width: 100%; border-radius: 16px; box-shadow: var(--shadow-xl); transition: transform 0.3s ease;">
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; margin-top: 80px; align-items: center;">
                <!-- Gestão Financeira -->
                <div data-aos="fade-right" data-aos-delay="100">
                    <div style="position: relative;">
                        <img src="/images/dashboard_financeiro.png" alt="Dashboard de Gestão Financeira" style="width: 100%; border-radius: 16px; box-shadow: var(--shadow-xl); transition: transform 0.3s ease;">
                    </div>
                </div>
                <div data-aos="fade-left" data-aos-delay="200">
                    <h3 style="font-family: 'Poppins', sans-serif; font-size: 2rem; font-weight: 700; margin-bottom: 24px; color: var(--gray-900);">
                        <i class="fas fa-chart-line" style="color: var(--primary); margin-right: 12px;"></i>
                        Gestão Financeira
                    </h3>
                    <p style="color: var(--gray-600); font-size: 18px; line-height: 1.6; margin-bottom: 32px;">
                        Dashboard financeiro completo com controle de receitas, despesas, fluxo de caixa e análises detalhadas da performance da sua clínica.
                    </p>
                    <ul style="list-style: none; margin-bottom: 32px;">
                        <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; color: var(--gray-700);">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            Dashboard com métricas em tempo real
                        </li>
                        <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; color: var(--gray-700);">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            Controle completo de receitas e despesas
                        </li>
                        <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; color: var(--gray-700);">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            Relatórios financeiros detalhados
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- AI Section -->
    <section class="ai-section" id="ai">
        <div class="container">
            <div class="ai-content">
                <div class="ai-text" data-aos="fade-right">
                    <h2>Assistente Médico com<br><span class="text-gradient">Inteligência Artificial</span></h2>
                    <p>Revolucione sua prática médica com nosso assistente de IA especializado em medicina, treinado com as melhores práticas e protocolos médicos atualizados.</p>

                    <ul class="ai-features">
                        <li><i class="fas fa-brain"></i> Sugestões de diagnósticos baseadas em sintomas</li>
                        <li><i class="fas fa-pills"></i> Recomendações de tratamentos personalizados</li>
                        <li><i class="fas fa-clipboard-check"></i> Lembretes de protocolos e diretrizes médicas</li>
                        <li><i class="fas fa-search"></i> Pesquisa instantânea em base de conhecimento médico</li>
                        <li><i class="fas fa-chart-bar"></i> Análise de dados clínicos e insights</li>
                        <li><i class="fas fa-calendar-check"></i> Otimização automática de agendas</li>
                    </ul>

                    <a href="#pricing" class="btn btn-primary">
                        <i class="fas fa-rocket"></i>
                        Experimentar IA Médica
                    </a>
                </div>

                <div class="ai-visual" data-aos="fade-left" data-aos-delay="200">
                    <img src="/images/chatbot1.png" alt="Interface do Assistente de IA Médica">
                    <img src="/images/chatbot2.png" alt="Conversa com Assistente de IA">
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Planos que Cabem no<br><span class="text-gradient">Seu Orçamento</span></h2>
                <p class="subtitle">Escolha o plano ideal para o porte da sua clínica e comece a transformar seu atendimento hoje mesmo</p>
            </div>

            <div class="pricing-grid">
                <div class="pricing-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="pricing-header">
                        <h3 class="pricing-name">Essencial</h3>
                        <div class="price">R$ 79<span class="price-period">/mês</span></div>
                        <p class="pricing-description">Perfeito para clínicas iniciantes que querem organizar e digitalizar seus processos básicos.</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><i class="fas fa-check"></i> <strong>100 consultas IA/mês</strong></li>
                            <li><i class="fas fa-check"></i> Dashboard completo</li>
                            <li><i class="fas fa-check"></i> Agenda digital avançada</li>
                            <li><i class="fas fa-check"></i> Prontuário eletrônico</li>
                            <li><i class="fas fa-check"></i> Controle de pacientes</li>
                            <li><i class="fas fa-check"></i> Controle de funcionários</li>
                            <li><i class="fas fa-check"></i> Controle de consultórios</li>
                            <li><i class="fas fa-check"></i> Múltiplos serviços</li>
                            <li><i class="fas fa-check"></i> Atestado com assinatura digital</li>
                            <li><i class="fas fa-check"></i> Até 2 usuários</li>
                        </ul>
                    </div>
                    <div class="additional-info">
                        <i class="fas fa-users"></i> Até 2 usuários inclusos
                    </div>
                    <div class="pricing-cta">
                        <a href="{{ route('register') }}" class="btn btn-outline">
                            <i class="fas fa-play"></i>
                            Começar Agora
                        </a>
                    </div>
                </div>

                <div class="pricing-card popular" data-aos="fade-up" data-aos-delay="200">
                    <div class="pricing-header">
                        <h3 class="pricing-name">Pro</h3>
                        <div class="price">R$ 149<span class="price-period">/mês</span></div>
                        <p class="pricing-description">Para clínicas em crescimento que precisam de automação e gestão financeira completa.</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><i class="fas fa-check"></i> <strong>400 consultas IA/mês</strong></li>
                            <li><i class="fas fa-check"></i> Tudo do plano Essencial</li>
                            <li><i class="fas fa-check"></i> Lembretes automáticos WhatsApp</li>
                            <li><i class="fas fa-check"></i> Módulo financeiro completo</li>
                            <li><i class="fas fa-check"></i> Controle de estoque</li>
                            <li><i class="fas fa-check"></i> Relatórios avançados</li>
                            <li><i class="fas fa-check"></i> Integração bancária</li>
                            <li><i class="fas fa-check"></i> Suporte prioritário</li>
                            <li><i class="fas fa-check"></i> Até 5 usuários</li>
                        </ul>
                    </div>
                    <div class="additional-info">
                        <i class="fas fa-users"></i> Até 5 usuários inclusos
                    </div>
                    <div class="pricing-cta">
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            <i class="fas fa-rocket"></i>
                            Testar Grátis
                        </a>
                    </div>
                </div>

                <div class="pricing-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="pricing-header">
                        <h3 class="pricing-name">Premium</h3>
                        <div class="price">R$ 249<span class="price-period">/mês</span></div>
                        <p class="pricing-description">Para clínicas inovadoras que querem o máximo de tecnologia, incluindo IA médica avançada.</p>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><i class="fas fa-check"></i> <strong>2000 consultas IA/mês</strong></li>
                            <li><i class="fas fa-check"></i> Tudo do plano Pro</li>
                            <li><i class="fas fa-check"></i> Campanhas de Marketing para WhatsApp</li>
                            <li><i class="fas fa-check"></i> Suporte prioritário 24/7</li>
                            <li><i class="fas fa-check"></i> Até 10 usuários</li>
                        </ul>
                    </div>
                    <div class="additional-info">
                        <i class="fas fa-users"></i> Até 10 usuários inclusos
                    </div>
                    <div class="pricing-cta">
                        <a href="{{ route('register') }}" class="btn btn-outline">
                            <i class="fas fa-crown"></i>
                            Experimentar Premium
                        </a>
                    </div>
                </div>
            </div>

            <!-- Custom Plans Notice -->
            <div class="custom-plan-notice" data-aos="fade-up" data-aos-delay="400">
                <div class="custom-plan-card">
                    <i class="fas fa-users-cog custom-plan-icon"></i>
                    <div class="custom-plan-content">
                        <h4 class="custom-plan-title">
                            Precisa de mais de 10 usuários?
                        </h4>
                        <p class="custom-plan-text">
                            Entre em contato para um plano personalizado para sua clínica
                        </p>
                    </div>
                    <a href="https://wa.me/5511992904529?text=Olá! Preciso de um plano personalizado" target="_blank" class="btn btn-primary custom-plan-btn">
                        <i class="fab fa-whatsapp"></i>
                        Fale Conosco
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Credits Section -->
    <section class="credits">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Créditos Adicionais de <span class="text-gradient">IA Médica</span></h2>
                <p class="subtitle">Expanda o uso do seu assistente de IA com pacotes de créditos adicionais</p>
            </div>

            <div class="credits-grid">
                <div class="credit-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="credit-title">Pacote +500 Consultas</div>
                    <div class="credit-price">R$ 59,90</div>
                    <p>500 consultas adicionais ao assistente de IA</p>
                    <div style="margin-top: 20px;">
                        <small style="color: var(--gray-600);">
                            <i class="fas fa-clock"></i> Válido por 30 dias
                        </small>
                    </div>
                </div>

                <div class="credit-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="credit-title">Pacote +2.000 Consultas</div>
                    <div class="credit-price">R$ 149,90</div>
                    <p>2.000 consultas adicionais ao assistente de IA</p>
                    <div style="margin-top: 12px;">
                        <span style="background: var(--success); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            💰 Economize R$ 99,70
                        </span>
                    </div>
                    <div style="margin-top: 8px;">
                        <small style="color: var(--gray-600);">
                            <i class="fas fa-clock"></i> Válido por 30 dias
                        </small>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 40px;" data-aos="fade-up" data-aos-delay="300">
                <p style="color: var(--gray-600); font-size: 14px;">
                    <i class="fas fa-info-circle" style="color: var(--primary);"></i>
                    Os créditos de IA são consumidos apenas quando você faz perguntas ao assistente médico
                </p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Médicos que <span class="text-gradient">Transformaram</span><br>suas Clínicas</h2>
                <p class="subtitle">Mais de 1.000 profissionais da saúde já revolucionaram seus consultórios com nossa plataforma</p>
            </div>

            <div class="testimonials-grid">
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                    <p class="testimonial-text">O ManagerClin transformou completamente a gestão da minha clínica. O assistente de IA me ajuda diariamente com sugestões precisas e o agendamento automático reduziu as faltas em 85%.</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">DR</div>
                        <div>
                            <div class="author-name">Dr. Ricardo Ferreira</div>
                            <div class="author-role">Cardiologista • São Paulo</div>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="200">
                    <p class="testimonial-text">A funcionalidade de telemedicina foi essencial durante a pandemia e continua sendo muito útil. A integração com o prontuário eletrônico é perfeita e economizo 2 horas por dia.</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">DA</div>
                        <div>
                            <div class="author-name">Dra. Ana Paula Costa</div>
                            <div class="author-role">Dermatologista • Rio de Janeiro</div>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="300">
                    <p class="testimonial-text">Os relatórios financeiros me ajudaram a identificar gargalos e aumentar a rentabilidade da minha clínica em 40%. O ROI foi incrível em apenas 3 meses.</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">DM</div>
                        <div>
                            <div class="author-name">Dr. Marcelo Oliveira</div>
                            <div class="author-role">Ortopedista • Belo Horizonte</div>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="400">
                    <p class="testimonial-text">O assistente de IA é revolucionário! Me ajuda com protocolos que eu poderia esquecer e sugere diagnósticos diferenciais que enriquecem minha análise clínica.</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">DC</div>
                        <div>
                            <div class="author-name">Dra. Carla Santos</div>
                            <div class="author-role">Pediatra • Porto Alegre</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta" id="contact">
        <div class="container cta-content">
            <h2 data-aos="fade-up">Pronto para Revolucionar<br>sua <span style="color: #a5b4fc;">Clínica</span>?</h2>
            <p data-aos="fade-up" data-aos-delay="100">Junte-se a mais de 1.000 médicos que já transformaram suas práticas com nossa plataforma. Teste grátis por 14 dias, sem compromisso.</p>

            <div data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register') }}" class="btn btn-glass" style="font-size: 18px; padding: 16px 32px;">
                    <i class="fas fa-rocket"></i>
                    Começar Teste Grátis Agora
                </a>
            </div>

            <div style="margin-top: 32px; opacity: 0.8;" data-aos="fade-up" data-aos-delay="300">
                <p style="font-size: 14px;">
                    <i class="fas fa-check-circle" style="color: #10b981; margin-right: 8px;"></i>
                    14 dias grátis • Sem cartão de crédito • Cancelamento a qualquer momento
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3 style="display: flex; align-items: center; gap: 12px;">
                        <svg width="24" height="24" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="20" cy="20" r="20" fill="url(#footerLogoGradient)" />
                            <rect x="17" y="10" width="6" height="20" rx="3" fill="white" />
                            <rect x="10" y="17" width="20" height="6" rx="3" fill="white" />
                            <path d="M8 20L12 16L16 24L20 12L24 28L28 16L32 20" stroke="rgba(255,255,255,0.8)" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="14" cy="14" r="1.5" fill="url(#footerLogoGradient)" />
                            <circle cx="26" cy="14" r="1.5" fill="url(#footerLogoGradient)" />
                            <circle cx="14" cy="26" r="1.5" fill="url(#footerLogoGradient)" />
                            <circle cx="26" cy="26" r="1.5" fill="url(#footerLogoGradient)" />
                            <defs>
                                <linearGradient id="footerLogoGradient" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#667eea" />
                                    <stop offset="0.5" stop-color="#764ba2" />
                                    <stop offset="1" stop-color="#f093fb" />
                                </linearGradient>
                            </defs>
                        </svg>
                        ManagerClin
                    </h3>
                    <p style="color: var(--gray-400); margin-bottom: 24px;">O sistema mais avançado e completo para gestão de clínicas e consultórios médicos, com IA integrada.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/managerclin/" aria-label="Instagram" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-column">
                    <h3>Produto</h3>
                    <ul class="footer-links">
                        <li><a href="#features">Recursos</a></li>
                        <li><a href="#ai">IA Médica</a></li>
                        <li><a href="#pricing">Planos e Preços</a></li>
                        <li><a href="#">Demonstração</a></li>
                        <li><a href="#">Integrações</a></li>
                        <li><a href="#">Segurança</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>Empresa</h3>
                    <ul class="footer-links">
                        <li><a href="#">Sobre Nós</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Carreiras</a></li>
                        <li><a href="#">Imprensa</a></li>
                        <li><a href="#contact">Contato</a></li>
                        <li><a href="#">Parceiros</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>Suporte</h3>
                    <ul class="footer-links">
                        <li><a href="#">Central de Ajuda</a></li>
                        <li><a href="#">Status do Sistema</a></li>
                        <li><a href="#">Documentação</a></li>
                        <li><a href="#">API</a></li>
                        <li><a href="#">Política de Privacidade</a></li>
                        <li><a href="#">Termos de Uso</a></li>
                    </ul>
                </div>
            </div>

            <div class="copyright">
                <p>&copy; 2024 ManagerClin. Todos os direitos reservados. Desenvolvido com ❤️ para profissionais da saúde.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Header scroll effect
        const header = document.getElementById('header');
        let lastScrollY = window.scrollY;

        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    const headerHeight = header.offsetHeight;
                    const targetPosition = targetElement.offsetTop - headerHeight - 20;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Counter animation
        const animateCounters = () => {
            const counters = document.querySelectorAll('.counter');
            const speed = 200;

            counters.forEach(counter => {
                const target = +counter.getAttribute('data-count');
                const count = +counter.innerText;
                const inc = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(() => animateCounters(), 1);
                } else {
                    counter.innerText = target;
                }
            });
        };

        // Trigger counter animation when stats section is visible
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        const statsSection = document.querySelector('.stats');
        if (statsSection) {
            observer.observe(statsSection);
        }

        // Mobile sidebar toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const mobileSidebarOverlay = document.getElementById('mobileSidebarOverlay');
        const mobileSidebarClose = document.getElementById('mobileSidebarClose');

        function openMobileSidebar() {
            mobileSidebar.classList.add('active');
            mobileSidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileSidebar() {
            mobileSidebar.classList.remove('active');
            mobileSidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        mobileMenuToggle?.addEventListener('click', openMobileSidebar);
        mobileSidebarClose?.addEventListener('click', closeMobileSidebar);
        mobileSidebarOverlay?.addEventListener('click', closeMobileSidebar);

        // Close mobile sidebar when clicking on a link
        mobileSidebar?.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', () => {
                closeMobileSidebar();
            });
        });

        // Add scroll reveal animation for elements
        const revealElements = document.querySelectorAll('.fade-in-up');

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        revealElements.forEach(el => revealObserver.observe(el));

        // Preloader
        window.addEventListener('load', () => {
            document.body.classList.add('loaded');
        });

        // Performance optimization - lazy load images
        if ('loading' in HTMLImageElement.prototype) {
            const images = document.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                img.src = img.dataset.src;
            });
        } else {
            // Fallback for browsers that don't support lazy loading
            const script = document.createElement('script');
            script.src = 'https://polyfill.io/v3/polyfill.min.js?features=IntersectionObserver';
            document.head.appendChild(script);
        }
    </script>
</body>

</html>
