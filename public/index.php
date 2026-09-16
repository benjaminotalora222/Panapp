<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PanApp | Sistema Inteligente para Panaderías</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🥐</text></svg>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* ══════════════════════════════════════════════════════════════
           VARIABLES & DESIGN SYSTEM
           ══════════════════════════════════════════════════════════════ */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #F97316;
            --primary-light: #FB923C;
            --primary-dark: #EA580C;
            --primary-glow: rgba(249, 115, 22, 0.35);
            --primary-soft: rgba(249, 115, 22, 0.08);
            
            --gold: #F59E0B;
            --gold-light: #FDE68A;
            --gold-dark: #D97706;
            
            --bg-body: #FAF6F0;
            --bg-surface: #FFFFFF;
            --bg-surface-elevated: #FFFFFF;
            --bg-alt: #F4ECE1;
            --bg-dark: #1A0F08;
            --bg-dark-card: #25160D;
            
            --text-main: #1C0F08;
            --text-muted: #705545;
            --text-light: #A38570;
            --text-white: #FFFFFF;
            
            --border-subtle: #EEDDCC;
            --border-focus: #F97316;
            --border-glass: rgba(249, 115, 22, 0.18);
            
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            --radius-full: 9999px;
            
            --shadow-sm: 0 2px 8px rgba(28, 15, 8, 0.04);
            --shadow-md: 0 10px 25px -5px rgba(28, 15, 8, 0.06), 0 4px 10px -2px rgba(28, 15, 8, 0.03);
            --shadow-lg: 0 20px 40px -12px rgba(249, 115, 22, 0.15), 0 8px 16px -4px rgba(28, 15, 8, 0.04);
            --shadow-glow: 0 12px 36px var(--primary-glow);
            
            --transition-bounce: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            --transition-smooth: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            background-image: 
                radial-gradient(circle at 100% 0%, rgba(249, 115, 22, 0.07) 0%, transparent 40%),
                radial-gradient(circle at 0% 50%, rgba(245, 158, 11, 0.05) 0%, transparent 45%),
                radial-gradient(rgba(249, 115, 22, 0.04) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 32px 32px;
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ══════════════════════════════════════════════════════════════
           PAGE LOADER & PROGRESS BAR
           ══════════════════════════════════════════════════════════════ */
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, #F97316, #F59E0B, #EA580C, #F97316);
            background-size: 300% 100%;
            z-index: 10000;
            transition: width 0.3s ease, opacity 0.4s ease;
            box-shadow: 0 0 12px rgba(249, 115, 22, 0.8);
            animation: loaderGradient 2s linear infinite;
        }
        @keyframes loaderGradient {
            0% { background-position: 0% 50%; }
            100% { background-position: 300% 50%; }
        }

        /* ══════════════════════════════════════════════════════════════
           SCROLL REVEAL SYSTEM
           ══════════════════════════════════════════════════════════════ */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1),
                        transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ══════════════════════════════════════════════════════════════
           FLOATING BACKGROUND PARTICLES
           ══════════════════════════════════════════════════════════════ */
        .bg-pattern {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .bg-icon {
            position: absolute;
            font-size: 36px;
            opacity: 0.05;
            user-select: none;
            filter: drop-shadow(0 4px 10px rgba(249, 115, 22, 0.2));
            animation: floatIconA 12s ease-in-out infinite;
        }
        .bg-icon:nth-child(even) {
            animation-name: floatIconB;
            animation-duration: 15s;
        }
        .bg-icon:nth-child(3n) {
            animation-name: floatIconC;
            animation-duration: 18s;
        }
        @keyframes floatIconA {
            0%, 100% { transform: translate(0, 0) rotate(0deg) scale(1); }
            33%      { transform: translate(12px, -24px) rotate(12deg) scale(1.08); }
            66%      { transform: translate(-10px, -10px) rotate(-8deg) scale(0.96); }
        }
        @keyframes floatIconB {
            0%, 100% { transform: translate(0, 0) rotate(0deg) scale(1); }
            40%      { transform: translate(-18px, -28px) rotate(-14deg) scale(1.1); }
            70%      { transform: translate(14px, -8px) rotate(10deg) scale(0.94); }
        }
        @keyframes floatIconC {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50%      { transform: translate(8px, -35px) rotate(18deg) scale(1.12); }
        }

        /* ══════════════════════════════════════════════════════════════
           NAVIGATION BAR
           ══════════════════════════════════════════════════════════════ */
        nav {
            position: sticky;
            top: 0;
            z-index: 500;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-subtle);
            padding: 0 48px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: var(--transition-smooth);
        }
        nav.scrolled {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 30px rgba(28, 15, 8, 0.05);
            border-bottom-color: rgba(249, 115, 22, 0.15);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            group: cursor-pointer;
        }
        .nav-logo {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 6px 18px rgba(249, 115, 22, 0.35);
            transition: var(--transition-bounce);
            position: relative;
            overflow: hidden;
        }
        .nav-logo::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 40%, rgba(255,255,255,0.4) 50%, transparent 60%);
            transform: rotate(30deg);
            transition: transform 0.6s ease;
        }
        .nav-brand:hover .nav-logo {
            transform: translateY(-2px) scale(1.06) rotate(6deg);
            box-shadow: 0 10px 25px rgba(249, 115, 22, 0.5);
        }
        .nav-brand:hover .nav-logo::after {
            transform: rotate(30deg) translate(80px, -80px);
        }

        .nav-name {
            font-family: 'Playfair Display', serif;
            font-size: 25px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }
        .nav-name span {
            color: var(--primary);
            font-style: italic;
            position: relative;
        }
        .nav-name span::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--gold));
            border-radius: 2px;
            opacity: 0.6;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-links a {
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-muted);
            padding: 9px 18px;
            border-radius: var(--radius-md);
            transition: var(--transition-smooth);
            position: relative;
        }
        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary-dark);
            background: var(--primary-soft);
        }
        
        .nav-links a.btn-nav {
            background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
            color: #FFFFFF !important;
            padding: 10px 24px;
            border-radius: var(--radius-md);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 6px 18px rgba(249, 115, 22, 0.35);
            transition: var(--transition-bounce);
            margin-left: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .nav-links a.btn-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(249, 115, 22, 0.5);
            background: linear-gradient(135deg, #FB923C 0%, #EA580C 100%);
        }
        .nav-links a.btn-nav i {
            transition: transform 0.3s ease;
        }
        .nav-links a.btn-nav:hover i {
            transform: rotate(-15deg) scale(1.15);
        }

        /* ══════════════════════════════════════════════════════════════
           HERO SECTION
           ══════════════════════════════════════════════════════════════ */
        .hero {
            position: relative;
            z-index: 1;
            min-height: calc(100vh - 72px);
            display: flex;
            align-items: center;
            padding: 60px 48px 40px;
            overflow: hidden;
        }
        
        /* Hero Ambient Light Blobs */
        .hero-glow-1 {
            position: absolute;
            width: 550px;
            height: 550px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.12) 0%, transparent 70%);
            top: -120px;
            left: -80px;
            pointer-events: none;
            border-radius: 50%;
            filter: blur(40px);
            animation: pulseGlow 8s ease-in-out infinite;
        }
        .hero-glow-2 {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.1) 0%, transparent 70%);
            bottom: -150px;
            right: -100px;
            pointer-events: none;
            border-radius: 50%;
            filter: blur(50px);
            animation: pulseGlow 10s ease-in-out infinite reverse;
        }
        @keyframes pulseGlow {
            0%, 100% { transform: scale(1) translate(0, 0); opacity: 0.8; }
            50%      { transform: scale(1.15) translate(20px, 15px); opacity: 1; }
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            align-items: center;
            gap: 60px;
        }

        .hero-left {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #FFFFFF;
            border: 1px solid rgba(249, 115, 22, 0.25);
            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.08);
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 8px 18px;
            border-radius: var(--radius-full);
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
            animation: heroFadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .hero-tag::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(249, 115, 22, 0.15), transparent);
            animation: tagSweep 3.5s ease-in-out infinite;
        }
        @keyframes tagSweep {
            0% { left: -100%; }
            40%, 100% { left: 180%; }
        }
        .hero-tag .dot {
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.25);
            animation: pulseDot 2s ease-in-out infinite;
        }
        @keyframes pulseDot {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.25); }
            50%      { transform: scale(1.2); box-shadow: 0 0 0 6px rgba(249, 115, 22, 0.1); }
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(38px, 5.2vw, 62px);
            line-height: 1.12;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.8px;
            animation: heroFadeUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) both 0.1s;
        }
        .hero h1 span {
            background: linear-gradient(135deg, #EA580C 0%, #F59E0B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-style: italic;
            display: inline-block;
        }

        .hero-left p {
            margin-top: 22px;
            font-size: 17px;
            font-weight: 500;
            color: var(--text-muted);
            max-width: 520px;
            line-height: 1.7;
            animation: heroFadeUp 0.75s cubic-bezier(0.16, 1, 0.3, 1) both 0.2s;
        }

        .hero-actions {
            margin-top: 36px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            animation: heroFadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both 0.3s;
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
            color: #FFFFFF;
            font-size: 16px;
            font-weight: 800;
            padding: 16px 36px;
            border-radius: var(--radius-lg);
            text-decoration: none;
            box-shadow: 0 12px 30px rgba(249, 115, 22, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.25);
            transition: var(--transition-bounce);
            position: relative;
            overflow: hidden;
        }
        .hero-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            transition: transform 0.6s ease;
        }
        .hero-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 18px 40px rgba(249, 115, 22, 0.55);
            background: linear-gradient(135deg, #FB923C 0%, #EA580C 100%);
        }
        .hero-btn:hover::after {
            transform: translateX(200%);
        }

        .hero-btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            color: var(--text-main);
            font-size: 15px;
            font-weight: 700;
            padding: 15px 28px;
            border-radius: var(--radius-lg);
            text-decoration: none;
            border: 1.5px solid var(--border-subtle);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
            transition: var(--transition-smooth);
        }
        .hero-btn-ghost:hover {
            border-color: var(--primary);
            color: var(--primary-dark);
            background: #FFFFFF;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.12);
        }
        .hero-btn-ghost i {
            transition: transform 0.3s ease;
            color: var(--primary);
        }
        .hero-btn-ghost:hover i {
            transform: translateY(3px);
        }

        /* Trust Badges */
        .hero-trust-row {
            margin-top: 32px;
            display: flex;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
            animation: heroFadeUp 0.85s cubic-bezier(0.16, 1, 0.3, 1) both 0.4s;
        }
        .trust-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
        }
        .trust-pill i {
            color: var(--primary);
            font-size: 14px;
        }

        /* Scroll Indicator */
        .scroll-indicator {
            margin-top: 40px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: heroFadeUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) both 0.5s;
        }
        .scroll-indicator span {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-light);
        }
        .scroll-mouse {
            width: 22px;
            height: 34px;
            border: 2px solid var(--border-subtle);
            border-radius: 12px;
            display: flex;
            justify-content: center;
            padding-top: 6px;
            background: #FFFFFF;
        }
        .scroll-mouse::before {
            content: '';
            width: 3px;
            height: 8px;
            background: var(--primary);
            border-radius: 2px;
            animation: mouseWheel 1.8s ease-in-out infinite;
        }
        @keyframes mouseWheel {
            0%   { transform: translateY(0); opacity: 1; }
            70%  { transform: translateY(10px); opacity: 0; }
            100% { transform: translateY(0); opacity: 0; }
        }

        @keyframes heroFadeUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ══════════════════════════════════════════════════════════════
           HERO RIGHT (INTERACTIVE 3D TILT STAGE)
           ══════════════════════════════════════════════════════════════ */
        .hero-right {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 1200px;
            animation: heroFadeUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) both 0.25s;
        }

        .hero-showcase {
            position: relative;
            width: 440px;
            height: 440px;
            display: flex;
            align-items: center;
            justify-content: center;
            transform-style: preserve-3d;
            transition: transform 0.4s ease-out;
        }

        /* Center Bakery Core */
        .hero-core {
            width: 270px;
            height: 270px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #FFFFFF 0%, #FFF5EB 50%, #FED7AA 100%);
            border: 3px solid rgba(249, 115, 22, 0.25);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 
                0 25px 60px rgba(249, 115, 22, 0.2),
                inset 0 0 40px rgba(255, 255, 255, 0.8),
                0 0 0 14px rgba(249, 115, 22, 0.04);
            position: relative;
            z-index: 2;
            animation: floatCore 6s ease-in-out infinite;
        }
        @keyframes floatCore {
            0%, 100% { transform: translateY(0) scale(1); }
            50%      { transform: translateY(-10px) scale(1.02); }
        }
        .hero-core-emoji {
            font-size: 80px;
            filter: drop-shadow(0 12px 20px rgba(234, 88, 12, 0.3));
            animation: coreEmojiBreath 4s ease-in-out infinite;
        }
        @keyframes coreEmojiBreath {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50%      { transform: scale(1.08) rotate(4deg); }
        }
        .hero-core-caption {
            font-size: 13px;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* Orbit Ring */
        .orbit-ring {
            position: absolute;
            width: 390px;
            height: 390px;
            border-radius: 50%;
            border: 2px dashed rgba(249, 115, 22, 0.22);
            pointer-events: none;
            animation: rotateOrbit 32s linear infinite;
        }
        @keyframes rotateOrbit {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        /* Orbiting Emoji Badges */
        .orbit-item {
            position: absolute;
            width: 48px;
            height: 48px;
            background: #FFFFFF;
            border-radius: 50%;
            border: 1.5px solid rgba(249, 115, 22, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 8px 20px rgba(28, 15, 8, 0.08);
            transform-origin: 0 0;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            animation: orbitMotion 18s linear infinite;
        }
        .orbit-item:hover {
            transform: scale(1.35) !important;
            box-shadow: 0 12px 28px rgba(249, 115, 22, 0.35);
            border-color: var(--primary);
            z-index: 10;
        }
        .orbit-item:nth-child(1) { animation-delay: 0s; }
        .orbit-item:nth-child(2) { animation-delay: -3s; }
        .orbit-item:nth-child(3) { animation-delay: -6s; }
        .orbit-item:nth-child(4) { animation-delay: -9s; }
        .orbit-item:nth-child(5) { animation-delay: -12s; }
        .orbit-item:nth-child(6) { animation-delay: -15s; }

        @keyframes orbitMotion {
            0%   { transform: rotate(0deg) translateX(195px) rotate(0deg); }
            100% { transform: rotate(360deg) translateX(195px) rotate(-360deg); }
        }

        /* Floating Glass Cards */
        .hero-float-badge {
            position: absolute;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(249, 115, 22, 0.2);
            border-radius: var(--radius-md);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 12px 30px rgba(28, 15, 8, 0.08);
            z-index: 4;
            white-space: nowrap;
            transition: var(--transition-bounce);
        }
        .hero-float-badge:hover {
            transform: translateY(-4px) scale(1.04);
            box-shadow: 0 16px 36px rgba(249, 115, 22, 0.18);
        }

        .badge-pos-1 {
            bottom: -15px;
            right: 0px;
            animation: floatBadgeA 5s ease-in-out infinite;
        }
        .badge-pos-2 {
            top: 10px;
            left: -30px;
            animation: floatBadgeB 6s ease-in-out infinite;
        }
        @keyframes floatBadgeA {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }
        @keyframes floatBadgeB {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(10px); }
        }

        .badge-icon-box {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #FFF7ED, #FFEDD5);
            border: 1px solid rgba(249, 115, 22, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .badge-icon-box.green {
            background: #ECFDF5;
            border-color: rgba(16, 185, 129, 0.3);
            color: #10B981;
        }
        .badge-text-box strong {
            display: block;
            font-size: 13px;
            font-weight: 800;
            color: var(--text-main);
        }
        .badge-text-box span {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
        }

        /* ══════════════════════════════════════════════════════════════
           STATS BAND (GLASS ELEVATED BAR)
           ══════════════════════════════════════════════════════════════ */
        .stats-wrapper {
            position: relative;
            z-index: 10;
            max-width: 1140px;
            margin: -20px auto 40px;
            padding: 0 24px;
        }
        .stats-band {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1.5px solid var(--border-subtle);
            border-radius: var(--radius-xl);
            padding: 30px 40px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }
        .stats-band::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #F97316, #F59E0B, #EA580C);
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 8px 16px;
            border-right: 1px solid var(--border-subtle);
            transition: var(--transition-smooth);
        }
        .stat-item:last-child {
            border-right: none;
        }
        .stat-item:hover {
            transform: translateY(-2px);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--primary-soft);
            border: 1px solid rgba(249, 115, 22, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--primary-dark);
            flex-shrink: 0;
            transition: var(--transition-bounce);
        }
        .stat-item:hover .stat-icon {
            transform: scale(1.12) rotate(6deg);
            background: var(--primary);
            color: #FFFFFF;
        }
        .stat-info {
            display: flex;
            flex-direction: column;
        }
        .stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 800;
            color: var(--primary-dark);
            line-height: 1;
        }
        .stat-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        /* ══════════════════════════════════════════════════════════════
           SECTION HEADERS
           ══════════════════════════════════════════════════════════════ */
        .section-header {
            text-align: center;
            margin-bottom: 56px;
            position: relative;
        }
        .section-eyebrow {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--primary-soft);
            padding: 6px 16px;
            border-radius: var(--radius-full);
            border: 1px solid rgba(249, 115, 22, 0.2);
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 4vw, 42px);
            color: var(--text-main);
            font-weight: 800;
            line-height: 1.2;
            margin-top: 6px;
        }
        .section-title span {
            color: var(--primary);
            font-style: italic;
        }
        .section-sub {
            margin-top: 14px;
            font-size: 16px;
            font-weight: 500;
            color: var(--text-muted);
            max-width: 520px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.65;
        }

        /* ══════════════════════════════════════════════════════════════
           CARDS SECTION (MÓDULOS)
           ══════════════════════════════════════════════════════════════ */
        .cards-section {
            position: relative;
            z-index: 1;
            padding: 80px 48px 100px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }
        .card {
            background: #FFFFFF;
            border: 1.5px solid var(--border-subtle);
            border-radius: var(--radius-xl);
            padding: 32px 26px 26px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-bounce);
        }
        .card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 100% 0%, rgba(249, 115, 22, 0.08) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #F97316, #F59E0B);
            opacity: 0;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease, opacity 0.4s ease;
        }
        .card:hover {
            transform: translateY(-10px);
            border-color: rgba(249, 115, 22, 0.4);
            box-shadow: var(--shadow-lg);
        }
        .card:hover::before {
            opacity: 1;
        }
        .card:hover::after {
            opacity: 1;
            transform: scaleX(1);
        }

        .card-icon-wrap {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            background: linear-gradient(135deg, #FFF7ED 0%, #FFEDD5 100%);
            border: 1.5px solid rgba(249, 115, 22, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin-bottom: 20px;
            box-shadow: 0 8px 18px rgba(249, 115, 22, 0.1);
            transition: var(--transition-bounce);
        }
        .card:hover .card-icon-wrap {
            transform: scale(1.15) rotate(-6deg);
            background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
            border-color: #EA580C;
            box-shadow: 0 10px 24px rgba(249, 115, 22, 0.35);
        }

        .card h3 {
            font-size: 19px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
            letter-spacing: -0.3px;
        }
        .card p {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
            line-height: 1.6;
            flex: 1;
            margin-bottom: 20px;
        }

        .card-features-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 20px;
            padding-top: 14px;
            border-top: 1px dashed var(--border-subtle);
        }
        .card-features-list li {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .card-features-list li i {
            font-size: 10px;
            color: #10B981;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 14px;
            border-top: 1px solid var(--border-subtle);
        }
        .badge {
            font-size: 11px;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: var(--radius-full);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .badge-emp   { background: #EDE9FE; color: #6D28D9; border: 1px solid #DDD6FE; }
        .badge-admin { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
        
        .arrow-action {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
            color: var(--primary);
            transition: var(--transition-smooth);
        }
        .card:hover .arrow-action {
            transform: translateX(4px);
            color: var(--primary-dark);
        }

        /* ══════════════════════════════════════════════════════════════
           WHY PANAPP / FEATURES SECTION
           ══════════════════════════════════════════════════════════════ */
        .features-section {
            position: relative;
            z-index: 1;
            background: #FFFFFF;
            border-top: 1px solid var(--border-subtle);
            border-bottom: 1px solid var(--border-subtle);
            padding: 100px 48px;
        }
        .features-inner {
            max-width: 1140px;
            margin: 0 auto;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
        }
        .feature-item {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 38px 32px;
            border-radius: var(--radius-xl);
            background: var(--bg-body);
            border: 1.5px solid var(--border-subtle);
            border-left: 5px solid var(--primary);
            transition: var(--transition-bounce);
            position: relative;
            overflow: hidden;
        }
        .feature-item:hover {
            background: #FFFFFF;
            border-color: rgba(249, 115, 22, 0.35);
            border-left-color: var(--primary-dark);
            box-shadow: var(--shadow-lg);
            transform: translateY(-6px);
        }

        .feature-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .feature-num {
            font-family: 'Playfair Display', serif;
            font-size: 44px;
            color: rgba(249, 115, 22, 0.25);
            line-height: 1;
            font-weight: 800;
            transition: var(--transition-smooth);
        }
        .feature-item:hover .feature-num {
            color: var(--primary);
            transform: scale(1.1);
        }
        .feature-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--primary-dark);
            background: var(--primary-soft);
            border-radius: var(--radius-full);
            padding: 4px 12px;
            border: 1px solid rgba(249, 115, 22, 0.2);
        }

        .feature-item h4 {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.3px;
        }
        .feature-item p {
            font-size: 15px;
            font-weight: 500;
            color: var(--text-muted);
            line-height: 1.7;
        }

        /* ══════════════════════════════════════════════════════════════
           CTA STRIP
           ══════════════════════════════════════════════════════════════ */
        .cta-strip {
            position: relative;
            z-index: 1;
            background: linear-gradient(135deg, #EA580C 0%, #F97316 50%, #D97706 100%);
            padding: 80px 48px;
            text-align: center;
            overflow: hidden;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 50px rgba(234, 88, 12, 0.25);
        }
        .cta-strip::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            top: -250px;
            left: -100px;
            animation: ctaOrb1 8s ease-in-out infinite;
        }
        .cta-strip::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -200px;
            right: -50px;
            animation: ctaOrb2 10s ease-in-out infinite;
        }
        @keyframes ctaOrb1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(30px, 20px) scale(1.1); }
        }
        @keyframes ctaOrb2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(-25px, -15px) scale(1.08); }
        }

        .cta-strip h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(30px, 4.5vw, 48px);
            font-weight: 800;
            color: #FFFFFF;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
            letter-spacing: -0.5px;
        }
        .cta-strip p {
            font-size: 17px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 36px;
            position: relative;
            z-index: 1;
            max-width: 550px;
            margin-left: auto;
            margin-right: auto;
        }
        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #FFFFFF;
            color: var(--primary-dark);
            font-weight: 800;
            font-size: 16px;
            padding: 18px 40px;
            border-radius: var(--radius-lg);
            text-decoration: none;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
            transition: var(--transition-bounce);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        .cta-btn:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.3);
            background: #FFFBF5;
            color: #C2410C;
        }
        .cta-btn i {
            transition: transform 0.3s ease;
        }
        .cta-btn:hover i {
            transform: rotate(-15deg) scale(1.2);
        }

        /* ══════════════════════════════════════════════════════════════
           FOOTER
           ══════════════════════════════════════════════════════════════ */
        footer {
            position: relative;
            z-index: 1;
            background: var(--bg-dark);
            color: var(--text-white);
            border-top: 1px solid rgba(249, 115, 22, 0.15);
            padding: 48px 48px 36px;
        }
        .footer-inner {
            max-width: 1140px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 24px;
        }
        .footer-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .footer-logo {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #F97316, #EA580C);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .footer-name {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: #FFFFFF;
        }
        .footer-name span {
            color: var(--primary-light);
            font-style: italic;
        }
        .footer-links {
            display: flex;
            gap: 24px;
        }
        .footer-links a {
            font-size: 14px;
            font-weight: 600;
            color: #C2A28A;
            text-decoration: none;
            transition: var(--transition-smooth);
        }
        .footer-links a:hover {
            color: var(--primary-light);
            transform: translateY(-1px);
        }
        .footer-copy {
            font-size: 13px;
            font-weight: 500;
            color: #8C6D58;
            text-align: center;
            padding-top: 28px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: 28px;
            max-width: 1140px;
            margin-left: auto;
            margin-right: auto;
        }
        .footer-copy span {
            color: var(--primary-light);
            font-weight: 700;
        }

        /* ══════════════════════════════════════════════════════════════
           CHATBOT WIDGET (PANBOT)
           ══════════════════════════════════════════════════════════════ */
        #chat-bubble {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 1000;
            width: 62px;
            height: 62px;
            background: linear-gradient(135deg, #F97316, #EA580C);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            cursor: pointer;
            box-shadow: 0 8px 30px rgba(249, 115, 22, 0.45);
            transition: var(--transition-bounce);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        #chat-bubble:hover {
            transform: scale(1.12) rotate(6deg);
            box-shadow: 0 14px 40px rgba(249, 115, 22, 0.6);
        }
        #chat-bubble .notif-dot {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 14px;
            height: 14px;
            background: #10B981;
            border-radius: 50%;
            border: 2.5px solid #FFFFFF;
            animation: pulseNotif 2s ease-in-out infinite;
        }
        @keyframes pulseNotif {
            0%, 100% { transform: scale(1); opacity: 1; }
            50%      { transform: scale(1.3); opacity: 0.8; }
        }

        #chat-window {
            position: fixed;
            bottom: 105px;
            right: 28px;
            z-index: 1000;
            width: 380px;
            max-height: 560px;
            height: 520px;
            background: #FFFFFF;
            border-radius: var(--radius-xl);
            box-shadow: 0 25px 70px rgba(28, 15, 8, 0.22);
            border: 1px solid var(--border-subtle);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transform: scale(0.85) translateY(30px);
            opacity: 0;
            pointer-events: none;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.25s ease;
        }
        #chat-window.open {
            transform: scale(1) translateY(0);
            opacity: 1;
            pointer-events: all;
        }

        .chat-header {
            background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(234, 88, 12, 0.2);
        }
        .chat-header-avatar {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            border: 1.5px solid rgba(255, 255, 255, 0.35);
        }
        .chat-header-info {
            flex: 1;
        }
        .chat-header-info strong {
            display: block;
            font-size: 15px;
            font-weight: 800;
            color: #FFFFFF;
        }
        .chat-header-info span {
            font-size: 12px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.85);
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .chat-header-info span .live-dot {
            width: 6px;
            height: 6px;
            background: #10B981;
            border-radius: 50%;
            display: inline-block;
        }
        .chat-header-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 10px;
            width: 32px;
            height: 32px;
            color: #FFFFFF;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-smooth);
        }
        .chat-header-close:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: scale(1.1);
        }

        #chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: #FFFDFB;
        }
        #chat-messages::-webkit-scrollbar {
            width: 5px;
        }
        #chat-messages::-webkit-scrollbar-thumb {
            background: #F3D5B5;
            border-radius: 5px;
        }

        .msg {
            display: flex;
            gap: 10px;
            max-width: 88%;
            animation: msgFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes msgFadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .msg.bot {
            align-self: flex-start;
        }
        .msg.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        .msg-avatar {
            width: 30px;
            height: 30px;
            border-radius: 10px;
            background: #FFF7ED;
            border: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .msg-bubble {
            padding: 10px 15px;
            border-radius: 16px;
            font-size: 13.5px;
            font-weight: 500;
            line-height: 1.55;
            box-shadow: var(--shadow-sm);
        }
        .msg.bot .msg-bubble {
            background: #FFFFFF;
            color: var(--text-main);
            border: 1px solid var(--border-subtle);
            border-bottom-left-radius: 4px;
        }
        .msg.user .msg-bubble {
            background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
            color: #FFFFFF;
            border-bottom-right-radius: 4px;
        }
        
        .msg-typing .msg-bubble {
            display: flex;
            gap: 5px;
            align-items: center;
            padding: 12px 18px;
            background: #FFFFFF;
            border: 1px solid var(--border-subtle);
        }
        .typing-dot {
            width: 7px;
            height: 7px;
            background: var(--primary);
            border-radius: 50%;
            animation: typingBounce 1.2s ease-in-out infinite;
        }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typingBounce {
            0%, 60%, 100% { transform: translateY(0); }
            30%           { transform: translateY(-6px); }
        }

        .chat-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 0 16px 12px;
            background: #FFFDFB;
        }
        .chat-suggestion {
            font-size: 12px;
            font-weight: 700;
            color: var(--primary-dark);
            background: #FFF7ED;
            border: 1px solid rgba(249, 115, 22, 0.25);
            border-radius: var(--radius-full);
            padding: 5px 14px;
            cursor: pointer;
            transition: var(--transition-smooth);
            white-space: nowrap;
        }
        .chat-suggestion:hover {
            background: #FFEDD5;
            border-color: var(--primary);
            transform: translateY(-1px);
        }

        .chat-footer {
            padding: 12px 16px;
            border-top: 1px solid var(--border-subtle);
            display: flex;
            gap: 10px;
            align-items: center;
            background: #FFFFFF;
        }
        #chat-input {
            flex: 1;
            padding: 10px 16px;
            background: #FAF6F0;
            border: 1.5px solid var(--border-subtle);
            border-radius: var(--radius-md);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text-main);
            outline: none;
            transition: var(--transition-smooth);
        }
        #chat-input:focus {
            border-color: var(--primary);
            background: #FFFFFF;
            box-shadow: 0 0 0 3px var(--primary-soft);
        }
        #chat-input::placeholder {
            color: var(--text-light);
        }
        #chat-send {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
            border: none;
            color: #FFFFFF;
            font-size: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-bounce);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }
        #chat-send:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 18px rgba(249, 115, 22, 0.45);
        }

        /* ══════════════════════════════════════════════════════════════
           RESPONSIVE BREAKPOINTS
           ══════════════════════════════════════════════════════════════ */
        @media (max-width: 1100px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); }
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .stats-band { grid-template-columns: repeat(2, 1fr); }
            .stat-item:nth-child(2) { border-right: none; }
            .hero-content {
                grid-template-columns: 1fr;
                gap: 50px;
            }
            .hero-left {
                align-items: center;
                text-align: center;
            }
            .hero-left p { max-width: 100%; }
            .hero-actions { justify-content: center; }
            .hero-trust-row { justify-content: center; }
            .scroll-indicator { justify-content: center; }
        }

        @media (max-width: 768px) {
            nav { padding: 0 20px; }
            .nav-links a:not(.btn-nav) { display: none; }
            .hero { padding: 40px 20px 30px; }
            .stats-wrapper { margin-top: 10px; padding: 0 16px; }
            .stats-band { grid-template-columns: 1fr; padding: 24px; }
            .stat-item { border-right: none; border-bottom: 1px solid var(--border-subtle); padding-bottom: 14px; }
            .stat-item:last-child { border-bottom: none; }
            .cards-section { padding: 50px 20px; }
            .cards-grid { grid-template-columns: 1fr; }
            .features-section { padding: 60px 20px; }
            .features-grid { grid-template-columns: 1fr; }
            .cta-strip { padding: 50px 20px; }
            footer { padding: 36px 20px 24px; }
            .footer-inner { flex-direction: column; align-items: flex-start; }
            
            .hero-showcase { width: 320px; height: 320px; }
            .hero-core { width: 200px; height: 200px; }
            .hero-core-emoji { font-size: 60px; }
            .orbit-ring { width: 280px; height: 280px; }
            .badge-pos-1 { right: -10px; bottom: -20px; }
            .badge-pos-2 { left: -10px; top: -10px; }
            
            @keyframes orbitMotion {
                0%   { transform: rotate(0deg) translateX(140px) rotate(0deg); }
                100% { transform: rotate(360deg) translateX(140px) rotate(-360deg); }
            }
        }

        @media (max-width: 440px) {
            #chat-window {
                width: calc(100vw - 32px);
                right: 16px;
                bottom: 95px;
                height: 480px;
            }
            #chat-bubble { right: 16px; bottom: 16px; }
        }
    </style>
</head>
<body>

    <!-- Barra de progreso al cargar -->
    <div id="page-loader"></div>

    <!-- Floating Background Bakery Pattern -->
    <div class="bg-pattern">
        <span class="bg-icon" style="top:6%; left:5%; animation-delay:0s">🥐</span>
        <span class="bg-icon" style="top:12%; left:88%; animation-delay:1.5s">🍞</span>
        <span class="bg-icon" style="top:28%; left:3%; animation-delay:3.2s">🧁</span>
        <span class="bg-icon" style="top:48%; left:93%; animation-delay:0.8s">🥖</span>
        <span class="bg-icon" style="top:68%; left:6%; animation-delay:2.4s">🍩</span>
        <span class="bg-icon" style="top:84%; left:87%; animation-delay:1.2s">🥐</span>
        <span class="bg-icon" style="top:20%; left:45%; animation-delay:2.9s">🧇</span>
        <span class="bg-icon" style="top:60%; left:48%; animation-delay:0.5s">🍰</span>
        <span class="bg-icon" style="top:38%; left:75%; animation-delay:1.9s">🥨</span>
        <span class="bg-icon" style="top:76%; left:26%; animation-delay:3.7s">🍪</span>
    </div>

    <!-- Ambient Glow Blobs -->
    <div class="hero-glow-1"></div>
    <div class="hero-glow-2"></div>

    <!-- NAVBAR -->
    <nav id="navbar">
        <a href="#" class="nav-brand">
            <div class="nav-logo">🥐</div>
            <div class="nav-name">Pan<span>App</span></div>
        </a>
        <div class="nav-links">
            <a href="#inicio" class="active">Inicio</a>
            <a href="#modulos">Módulos</a>
            <a href="#por-que">¿Por qué?</a>
            <a href="/PanApp/views/usuarios/login.php" class="btn-nav">
                <i class="fas fa-key"></i> Iniciar sesión
            </a>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero" id="inicio">
        <div class="hero-content">
            <div class="hero-left">
                <div class="hero-tag">
                    <span class="dot"></span>
                    🥖 Sistema Integral para Panaderías
                </div>
                
                <h1>Tu panadería, bajo control...<br><span>Ventas, inventario y reportes en un solo lugar.</span></h1>
                
                <p>Optimiza la atención al cliente, controla tus ingredientes y productos terminados en tiempo real, y visualiza el crecimiento diario de tu negocio. Hecho con ❤️ para panaderías colombianas.</p>
                
                <div class="hero-actions">
                    <a href="/PanApp/views/usuarios/login.php" class="hero-btn">
                        <i class="fas fa-arrow-right-to-bracket"></i> Ingresar ahora
                    </a>
                    <a href="#modulos" class="hero-btn-ghost">
                        <i class="fas fa-arrow-down"></i> Ver módulos
                    </a>
                </div>

                <!-- Trust Micro-Badges -->
                <div class="hero-trust-row">
                    <div class="trust-pill">
                        <i class="fas fa-bolt"></i> Cobros en 5s
                    </div>
                    <div class="trust-pill">
                        <i class="fas fa-shield-halved"></i> 100% Seguro
                    </div>
                    <div class="trust-pill">
                        <i class="fas fa-mobile-screen-button"></i> Multi-dispositivo
                    </div>
                </div>

                <!-- Scroll Indicator -->
                <div class="scroll-indicator">
                    <div class="scroll-mouse"></div>
                    <span>Desliza para explorar</span>
                </div>
            </div>

            <!-- Hero Right Visual (Interactive 3D Stage) -->
            <div class="hero-right">
                <div class="hero-showcase" id="heroShowcase">
                    <!-- Orbit Ring -->
                    <div class="orbit-ring"></div>
                    
                    <!-- Orbiting Bakery Icons -->
                    <div class="orbit-item" title="Croissant recién horneado">🥐</div>
                    <div class="orbit-item" title="Pan tajado y artesanal">🍞</div>
                    <div class="orbit-item" title="Baguette crujiente">🥖</div>
                    <div class="orbit-item" title="Cupcakes y pastelería">🧁</div>
                    <div class="orbit-item" title="Donas glaseadas">🍩</div>
                    <div class="orbit-item" title="Tortas especiales">🎂</div>

                    <!-- Center Core -->
                    <div class="hero-core">
                        <span class="hero-core-emoji">🥐</span>
                        <span class="hero-core-caption">PanApp Suite</span>
                    </div>

                    <!-- Floating Metric 1 (Top Left) -->
                    <div class="hero-float-badge badge-pos-2">
                        <div class="badge-icon-box green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="badge-text-box">
                            <strong>Sistema Activo 24/7</strong>
                            <span>Disponibilidad inmediata</span>
                        </div>
                    </div>

                    <!-- Floating Metric 2 (Bottom Right) -->
                    <div class="hero-float-badge badge-pos-1">
                        <div class="badge-icon-box">
                            📈
                        </div>
                        <div class="badge-text-box">
                            <strong>+350 Ventas registradas</strong>
                            <span>Flujo ágil y sin papel</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- STATS BAND -->
    <div class="stats-wrapper reveal">
        <div class="stats-band">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-num" data-count="500" data-prefix="+">+500</span>
                    <span class="stat-label">Ventas registradas</span>
                </div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-num" data-count="100" data-suffix="%">100%</span>
                    <span class="stat-label">Cero papel & digital</span>
                </div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-num" data-count="6" data-suffix="+">6+</span>
                    <span class="stat-label">Módulos integrados</span>
                </div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-num">24/7</span>
                    <span class="stat-label">Acceso garantizado</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MÓDULOS / CARDS SECTION -->
    <section class="cards-section" id="modulos">
        <div class="section-header reveal">
            <span class="section-eyebrow"><i class="fas fa-layer-group"></i> Módulos del Sistema</span>
            <h2 class="section-title">¿Qué quieres gestionar <span>hoy?</span></h2>
            <p class="section-sub">Accede de forma rápida y segura a cada herramienta diseñada para la operatividad de tu negocio.</p>
        </div>

        <div class="cards-grid">
            <!-- Card 1: Ventas -->
            <a href="/PanApp/views/usuarios/login.php" class="card reveal" style="transition-delay: 0.05s">
                <div class="card-icon-wrap">🛒</div>
                <h3>Registrar Venta</h3>
                <p>Agrega productos al carrito al instante, calcula totales y cobra con múltiples métodos de pago.</p>
                <ul class="card-features-list">
                    <li><i class="fas fa-circle-check"></i> Cobro rápido en caja</li>
                    <li><i class="fas fa-circle-check"></i> Historial y comprobantes</li>
                </ul>
                <div class="card-footer">
                    <span class="badge badge-emp">Cajero / Admin</span>
                    <span class="arrow-action">Ingresar <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>

            <!-- Card 2: Inventario -->
            <a href="/PanApp/views/usuarios/login.php" class="card reveal" style="transition-delay: 0.12s">
                <div class="card-icon-wrap">📦</div>
                <h3>Inventario & Insumos</h3>
                <p>Monitorea existencias de harina, azúcar y productos horneados. Recibe alertas de stock crítico.</p>
                <ul class="card-features-list">
                    <li><i class="fas fa-circle-check"></i> Entradas y salidas en vivo</li>
                    <li><i class="fas fa-circle-check"></i> Control de insumos</li>
                </ul>
                <div class="card-footer">
                    <span class="badge badge-admin">Admin</span>
                    <span class="arrow-action">Ingresar <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>

            <!-- Card 3: Reportes -->
            <a href="/PanApp/views/usuarios/login.php" class="card reveal" style="transition-delay: 0.19s">
                <div class="card-icon-wrap">📊</div>
                <h3>Reportes & Análisis</h3>
                <p>Visualiza métricas del día, semanas y meses. Conoce tus panes más vendidos y exporta a PDF.</p>
                <ul class="card-features-list">
                    <li><i class="fas fa-circle-check"></i> Gráficas de rendimiento</li>
                    <li><i class="fas fa-circle-check"></i> Exportación PDF inmediata</li>
                </ul>
                <div class="card-footer">
                    <span class="badge badge-admin">Admin</span>
                    <span class="arrow-action">Ingresar <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>

            <!-- Card 4: Usuarios -->
            <a href="/PanApp/views/usuarios/login.php" class="card reveal" style="transition-delay: 0.26s">
                <div class="card-icon-wrap">👥</div>
                <h3>Gestión de Usuarios</h3>
                <p>Administra perfiles de colaboradores, roles de acceso seguro y auditoría de operaciones.</p>
                <ul class="card-features-list">
                    <li><i class="fas fa-circle-check"></i> Control de permisos</li>
                    <li><i class="fas fa-circle-check"></i> Sesiones protegidas</li>
                </ul>
                <div class="card-footer">
                    <span class="badge badge-admin">Admin</span>
                    <span class="arrow-action">Ingresar <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
        </div>
    </section>

    <!-- WHY PANAPP / FEATURES SECTION -->
    <section class="features-section" id="por-que">
        <div class="features-inner">
            <div class="section-header reveal">
                <span class="section-eyebrow"><i class="fas fa-star"></i> Ventajas Clave</span>
                <h2 class="section-title">Hecho para el ritmo real<br>de tu <span>panadería</span></h2>
                <p class="section-sub">Diseñado meticulosamente para eliminar fricciones operativas y darte total tranquilidad contable.</p>
            </div>

            <div class="features-grid">
                <!-- Feature 1 -->
                <div class="feature-item reveal" style="transition-delay: 0.05s">
                    <div class="feature-top">
                        <div class="feature-num">01</div>
                        <span class="feature-tag">⚡ Rapidez</span>
                    </div>
                    <h4>Cobros en cuestión de segundos</h4>
                    <p>El mostrador no se puede detener. La interfaz de venta es ultraliviana para registrar pedidos a la velocidad de la hora pico sin trabas.</p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-item reveal" style="transition-delay: 0.12s">
                    <div class="feature-top">
                        <div class="feature-num">02</div>
                        <span class="feature-tag">📦 Precisión</span>
                    </div>
                    <h4>Inventario en tiempo real</h4>
                    <p>Cada producto vendido descuenta automáticamente sus cantidades. Evita quedarte sin existencias de ingredientes clave para hornear.</p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-item reveal" style="transition-delay: 0.19s">
                    <div class="feature-top">
                        <div class="feature-num">03</div>
                        <span class="feature-tag">📊 Inteligencia</span>
                    </div>
                    <h4>Reportes con datos accionables</h4>
                    <p>Conoce qué productos generan mayores ingresos, tus horas con mayor afluencia y el balance mensual para tomar decisiones informadas.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA STRIP -->
    <section class="cta-strip reveal">
        <h2>¿Todo listo para impulsar tu panadería?</h2>
        <p>Inicia sesión en tu panel de control y gestiona cada detalle de tu negocio desde cualquier dispositivo.</p>
        <a href="/PanApp/views/usuarios/login.php" class="cta-btn">
            <i class="fas fa-key"></i> Ingresar al Sistema
        </a>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-inner">
            <a href="#" class="footer-brand">
                <div class="footer-logo">🥐</div>
                <div class="footer-name">Pan<span>App</span></div>
            </a>
            <div class="footer-links">
                <a href="#inicio">Inicio</a>
                <a href="#modulos">Módulos</a>
                <a href="#por-que">¿Por qué PanApp?</a>
                <a href="/PanApp/views/usuarios/login.php">Iniciar Sesión</a>
            </div>
        </div>
        <p class="footer-copy">
            <span>PanApp</span> &nbsp;·&nbsp; Software de Gestión para Panaderías Colombianas &nbsp;·&nbsp; <?php echo date('Y'); ?>
        </p>
    </footer>

    <!-- CHATBOT WIDGET (PANBOT) -->
    <!-- Botón flotante -->
    <button id="chat-bubble" onclick="toggleChat()" title="Hablar con PanBot">
        <span id="chat-bubble-icon">🤖</span>
        <span class="notif-dot"></span>
    </button>

    <!-- Ventana del chat -->
    <div id="chat-window">
        <div class="chat-header">
            <div class="chat-header-avatar">🤖</div>
            <div class="chat-header-info">
                <strong>PanBot</strong>
                <span><span class="live-dot"></span> En línea · Asistente PanApp</span>
            </div>
            <button class="chat-header-close" onclick="toggleChat()">✕</button>
        </div>

        <div id="chat-messages"></div>

        <div class="chat-suggestions" id="chat-suggestions">
            <span class="chat-suggestion" onclick="sendSuggestion('¿Qué es PanApp?')">¿Qué es PanApp?</span>
            <span class="chat-suggestion" onclick="sendSuggestion('¿Cómo ingreso al sistema?')">¿Cómo ingreso?</span>
            <span class="chat-suggestion" onclick="sendSuggestion('¿Qué módulos tiene?')">Módulos</span>
            <span class="chat-suggestion" onclick="sendSuggestion('¿Cómo registro una venta?')">Ventas</span>
        </div>

        <div class="chat-footer">
            <input type="text" id="chat-input" placeholder="Escribe tu pregunta sobre PanApp..." maxlength="300"
                   onkeydown="if(event.key==='Enter')sendMessage()">
            <button id="chat-send" onclick="sendMessage()" title="Enviar mensaje">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         INTERACTIVE SCRIPTS & ANIMATIONS
         ══════════════════════════════════════════════════════════════ -->
    <script>
        // Prevenir modo edición accidental
        document.designMode = 'off';
        document.querySelectorAll('[contenteditable]').forEach(el => el.removeAttribute('contenteditable'));

        // 1. Barra de progreso al cargar
        const loader = document.getElementById('page-loader');
        let progress = 0;
        const loaderInterval = setInterval(() => {
            progress += Math.random() * 22;
            if (progress >= 92) {
                progress = 92;
                clearInterval(loaderInterval);
            }
            if (loader) loader.style.width = progress + '%';
        }, 70);

        window.addEventListener('load', () => {
            clearInterval(loaderInterval);
            if (loader) {
                loader.style.width = '100%';
                setTimeout(() => { loader.style.opacity = '0'; }, 300);
                setTimeout(() => { loader.style.display = 'none'; }, 700);
            }
        });

        // 2. Navbar glassmorphic scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 30) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }, { passive: true });

        // 3. Scroll reveal con IntersectionObserver
        const observerOptions = {
            threshold: 0.12,
            rootMargin: '0px 0px -50px 0px'
        };
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

        // 4. Contadores dinámicos animados en Stats Band
        function animateCounter(el) {
            const target = parseInt(el.dataset.count, 10);
            if (isNaN(target)) return;
            const prefix = el.dataset.prefix || '';
            const suffix = el.dataset.suffix || '';
            const duration = 1600;
            let startTime = null;

            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                const elapsed = timestamp - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Easing out quadratic
                const easeProgress = 1 - (1 - progress) * (1 - progress);
                const currentVal = Math.floor(easeProgress * target);

                el.textContent = prefix + currentVal + suffix;

                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    el.textContent = prefix + target + suffix;
                }
            }
            requestAnimationFrame(step);
        }

        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.querySelectorAll('.stat-num[data-count]').forEach(animateCounter);
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        const statsBand = document.querySelector('.stats-band');
        if (statsBand) statsObserver.observe(statsBand);

        // 5. Active nav link on scroll
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-links a:not(.btn-nav)');
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(s => {
                if (window.scrollY >= s.offsetTop - 140) {
                    current = s.id;
                }
            });
            navLinks.forEach(a => {
                a.classList.remove('active');
                if (a.getAttribute('href') === '#' + current) {
                    a.classList.add('active');
                }
            });
        }, { passive: true });

        // 6. Interactive 3D Tilt for Hero Showcase on Mousemove
        const heroRight = document.querySelector('.hero-right');
        const heroShowcase = document.getElementById('heroShowcase');
        if (heroRight && heroShowcase && window.innerWidth > 900) {
            heroRight.addEventListener('mousemove', (e) => {
                const rect = heroRight.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                const tiltX = (y / (rect.height / 2)) * -12;
                const tiltY = (x / (rect.width / 2)) * 12;
                heroShowcase.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale(1.02)`;
            });

            heroRight.addEventListener('mouseleave', () => {
                heroShowcase.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale(1)';
            });
        }

        // 7. Ripple Effect en botones primarios y CTA
        document.querySelectorAll('.hero-btn, .cta-btn, .btn-nav').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height) * 2;
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.35);
                    width: ${size}px;
                    height: ${size}px;
                    left: ${e.clientX - rect.left - size/2}px;
                    top: ${e.clientY - rect.top - size/2}px;
                    transform: scale(0);
                    animation: dynamicRipple 0.6s ease-out forwards;
                    pointer-events: none;
                `;
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 650);
            });
        });

        // Dynamic ripple animation style injection
        (function() {
            const style = document.createElement('style');
            style.textContent = '@keyframes dynamicRipple { to { transform: scale(1); opacity: 0; } }';
            document.head.appendChild(style);
        })();

        // 8. CHATBOT LOGIC
        let chatOpen = false;
        let chatInitialized = false;

        function toggleChat() {
            chatOpen = !chatOpen;
            const win = document.getElementById('chat-window');
            const icon = document.getElementById('chat-bubble-icon');
            const notifDot = document.querySelector('#chat-bubble .notif-dot');

            if (chatOpen) {
                win.classList.add('open');
                icon.textContent = '✕';
                if (notifDot) notifDot.style.display = 'none';

                if (!chatInitialized) {
                    chatInitialized = true;
                    setTimeout(() => {
                        addBotMessage('¡Hola! 👋 Soy <strong>PanBot</strong>, el asistente inteligente de <strong>PanApp</strong>. ¿En qué puedo orientarte hoy? 🥐');
                    }, 250);
                }
                setTimeout(() => {
                    const input = document.getElementById('chat-input');
                    if (input) input.focus();
                }, 300);
            } else {
                win.classList.remove('open');
                icon.textContent = '🤖';
            }
        }

        function addBotMessage(html) {
            const msgs = document.getElementById('chat-messages');
            const div = document.createElement('div');
            div.className = 'msg bot';
            div.innerHTML = `<div class="msg-avatar">🤖</div><div class="msg-bubble">${html}</div>`;
            msgs.appendChild(div);
            msgs.scrollTop = msgs.scrollHeight;
        }

        function addUserMessage(text) {
            const msgs = document.getElementById('chat-messages');
            const div = document.createElement('div');
            div.className = 'msg user';
            div.innerHTML = `<div class="msg-bubble">${escapeHtml(text)}</div>`;
            msgs.appendChild(div);
            msgs.scrollTop = msgs.scrollHeight;
        }

        function showTyping() {
            const msgs = document.getElementById('chat-messages');
            const div = document.createElement('div');
            div.className = 'msg bot msg-typing';
            div.id = 'typing-indicator';
            div.innerHTML = `<div class="msg-avatar">🤖</div><div class="msg-bubble"><span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span></div>`;
            msgs.appendChild(div);
            msgs.scrollTop = msgs.scrollHeight;
        }

        function removeTyping() {
            const t = document.getElementById('typing-indicator');
            if (t) t.remove();
        }

        function escapeHtml(text) {
            return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function sendSuggestion(text) {
            const sugg = document.getElementById('chat-suggestions');
            if (sugg) sugg.style.display = 'none';
            document.getElementById('chat-input').value = text;
            sendMessage();
        }

        function sendMessage() {
            const input = document.getElementById('chat-input');
            const msg = input.value.trim();
            if (!msg) return;

            input.value = '';
            const sugg = document.getElementById('chat-suggestions');
            if (sugg) sugg.style.display = 'none';

            addUserMessage(msg);
            showTyping();

            fetch('/PanApp/controllers/ChatbotController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mensaje: msg })
            })
            .then(r => r.json())
            .then(data => {
                removeTyping();
                addBotMessage(data.reply || 'No pude procesar tu pregunta. Intenta de nuevo.');
            })
            .catch(() => {
                removeTyping();
                addBotMessage('Hubo un error de conexión con el asistente. Intenta de nuevo. 🔌');
            });
        }
    </script>
</body>
</html>
