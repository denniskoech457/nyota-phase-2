<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nyota Limit Boost</title>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const mobileBtn = document.querySelector('.mobile-menu-btn');
      const mobileMenu = document.querySelector('.mobile-drawer');
      const drawerLinks = document.querySelectorAll('.mobile-drawer a');
      const appPage = document.getElementById('application-page');
      const homePage = document.getElementById('home-page');
      const progressFill = document.querySelector('.progress-fill');
      const progressText = document.querySelector('.progress-pill');
      const stepNumber = document.getElementById('step-number');
      const stepItems = Array.from(document.querySelectorAll('.step-item'));
      const formSteps = Array.from(document.querySelectorAll('.form-step'));
      const nextBtns = document.querySelectorAll('[data-next]');
      const prevBtns = document.querySelectorAll('[data-prev]');
      const applyLinks = document.querySelectorAll('[data-open-apply]');
      const logoHome = document.querySelectorAll('[data-go-home]');
      const opportunityCards = Array.from(document.querySelectorAll('.opportunity-card'));
      const submitBtn = document.getElementById('submit-application');
      const preloaderScreen = document.getElementById('preloader-screen');
      const checkoutForm = document.getElementById('checkoutForm');

      let currentStep = 0;

      function setView(view) {
        const showApply = view === 'apply';
        if (homePage) homePage.style.display = showApply ? 'none' : 'block';
        if (appPage) appPage.style.display = showApply ? 'block' : 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }

      function updateSteps() {
        const widths = ['33%', '67%', '100%'];
        const labels = ['33% Complete', '67% Complete', '100% Complete'];

        if (progressFill) progressFill.style.width = widths[currentStep];
        if (progressText) progressText.textContent = labels[currentStep];
        if (stepNumber) stepNumber.textContent = String(currentStep + 1);

        stepItems.forEach((item, index) => {
          item.classList.toggle('active', index === currentStep);
          item.classList.toggle('done', index < currentStep);
        });

        formSteps.forEach((step, index) => {
          step.style.display = index === currentStep ? 'block' : 'none';
        });
      }

      if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', () => {
          mobileMenu.classList.toggle('open');
        });
      }

      drawerLinks.forEach(link => {
        link.addEventListener('click', () => mobileMenu?.classList.remove('open'));
      });

      applyLinks.forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          setView('apply');
        });
      });

      logoHome.forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          setView('home');
        });
      });

      nextBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          if (currentStep < formSteps.length - 1) {
            currentStep += 1;
            updateSteps();
          }
        });
      });

      prevBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          if (currentStep > 0) {
            currentStep -= 1;
            updateSteps();
          }
        });
      });

      opportunityCards.forEach(card => {
        card.addEventListener('click', () => {
          opportunityCards.forEach(c => c.classList.remove('selected'));
          card.classList.add('selected');
          const radio = card.querySelector('input[type="radio"]');
          if (radio) radio.checked = true;
        });
      });

      if (submitBtn && preloaderScreen && checkoutForm) {
        submitBtn.addEventListener('click', (e) => {
          e.preventDefault();

          const preloaderFill = document.querySelector('.preloader-fill');
          const preloaderPercent = document.querySelector('.preloader-percent');
          const preloaderStatusText = document.querySelector('.preloader-status-text');
          const preloaderGreeting = document.querySelector('.preloader-greeting');

          const fullNameInput = checkoutForm.querySelector('[name="full_name"]');
          const statuses = [
            'Analyzing your profile',
            'Calculating eligibility',
            'Risk Assessment',
            'Finalizing offer'
          ];

          const applicantName = fullNameInput && fullNameInput.value.trim()
            ? fullNameInput.value.trim()
            : 'Applicant';

          if (preloaderGreeting) {
            preloaderGreeting.textContent = `Hello ${applicantName}, we're calculating your loan eligibility...`;
          }

          preloaderScreen.style.display = 'flex';

          let progress = 0;
          let statusIndex = 0;

          if (preloaderFill) preloaderFill.style.width = '0%';
          if (preloaderPercent) preloaderPercent.textContent = '0% Complete';
          if (preloaderStatusText) preloaderStatusText.textContent = statuses[0];

          const progressInterval = setInterval(() => {
            progress += 2;
            if (progress > 100) progress = 100;

            if (preloaderFill) preloaderFill.style.width = progress + '%';
            if (preloaderPercent) preloaderPercent.textContent = progress + '% Complete';

            if (progress >= 20 && progress < 45) statusIndex = 1;
            else if (progress >= 45 && progress < 75) statusIndex = 2;
            else if (progress >= 75) statusIndex = 3;
            else statusIndex = 0;

            if (preloaderStatusText) {
              preloaderStatusText.textContent = statuses[statusIndex];
            }

            if (progress >= 100) {
              clearInterval(progressInterval);
              setTimeout(() => {
                checkoutForm.submit();
              }, 350);
            }
          }, 45);
        });
      }

      updateSteps();
    });
  </script>

  <style>
    :root {
      --bg: #f7f7f8;
      --text: #1f2937;
      --muted: #667085;
      --border: #dfe3e8;
      --green: #4d8b3f;
      --green-dark: #3f7334;
      --red: #c81e4b;
      --blue: #6679a5;
      --card: #edf4ea;
      --white: #ffffff;
      --shadow: 0 8px 24px rgba(16, 24, 40, 0.06);
      --max: 1180px;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: Inter, Arial, Helvetica, sans-serif;
      background: var(--bg);
      color: var(--text);
      line-height: 1.5;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    .container {
      width: min(100% - 32px, var(--max));
      margin: 0 auto;
    }

    .navbar {
      position: sticky;
      top: 0;
      z-index: 30;
      background: rgba(255, 255, 255, 0.94);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--border);
    }

    .nav-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      min-height: 72px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 700;
      font-size: 1.75rem;
    }

    .brand-logo {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      background: linear-gradient(135deg, var(--green), #c8d94f, var(--red));
      box-shadow: var(--shadow);
    }

    .country-pill {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 4px 8px;
      border-radius: 999px;
      background: #f1f3f5;
      font-size: 0.75rem;
      font-weight: 700;
      color: #525866;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 28px;
      color: #55637a;
      font-size: 0.98rem;
    }

    .nav-cta {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 46px;
      padding: 0 18px;
      border-radius: 8px;
      background: var(--green);
      color: white;
      font-weight: 700;
      transition: 0.2s ease;
    }

    .nav-cta:hover,
    .hero-primary:hover {
      background: var(--green-dark);
    }

    .hero {
      padding: 54px 0 34px;
      text-align: center;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border: 1px solid #b7cab1;
      border-radius: 999px;
      background: #eef5eb;
      color: var(--green-dark);
      font-size: 0.95rem;
      margin-bottom: 24px;
    }

    .hero h1 {
      margin: 0;
      font-size: clamp(2.6rem, 6vw, 5rem);
      line-height: 1.02;
      font-weight: 800;
      letter-spacing: -0.03em;
    }

    .hero h1 .green { color: var(--green); }
    .hero h1 .red { color: var(--red); }

    .hero h2 {
      margin: 22px 0 14px;
      font-size: clamp(1.7rem, 3vw, 3rem);
      line-height: 1.15;
      color: var(--blue);
      font-weight: 800;
    }

    .hero p {
      margin: 0 auto 14px;
      max-width: 780px;
      color: #62708a;
      font-size: clamp(1rem, 1.5vw, 1.2rem);
    }

    .hero p strong {
      color: #111827;
    }

    .hero-note {
      margin-top: 10px;
      color: #6b7280;
      font-size: 1rem;
    }

    .hero-actions {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
      margin-top: 26px;
    }

    .hero-primary,
    .hero-secondary {
      width: min(100%, 430px);
      min-height: 58px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      font-weight: 800;
      border: 1px solid var(--border);
      transition: 0.2s ease;
    }

    .hero-primary {
      background: var(--green);
      color: white;
      border-color: transparent;
      gap: 10px;
    }

    .hero-secondary {
      background: white;
      color: #111827;
    }

    .spotlight-card {
      margin: 32px auto 0;
      width: min(100%, 900px);
      background: var(--card);
      border: 1px solid #cddac8;
      border-radius: 12px;
      padding: 48px 24px 34px;
      box-shadow: var(--shadow);
    }

    .spotlight-logo {
      width: 96px;
      height: 64px;
      margin: 0 auto 20px;
      border-radius: 4px;
      background: linear-gradient(135deg, #f4f4f4, #ffffff);
      border: 1px solid #e5e7eb;
      display: grid;
      place-items: center;
      font-size: 0.7rem;
      font-weight: 800;
      color: #64748b;
      text-align: center;
      padding: 8px;
    }

    .spotlight-card h3 {
      margin: 0;
      font-size: clamp(1.8rem, 2vw, 2.4rem);
      line-height: 1.2;
    }

    .spotlight-card p {
      margin: 10px 0 24px;
      color: #6a7895;
      font-size: 1.05rem;
    }

    .tags {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
    }

    .tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      border: 1px solid #b9ccb2;
      border-radius: 999px;
      background: #f2f8ef;
      color: var(--green-dark);
      font-size: 0.96rem;
      font-weight: 600;
    }

    .mobile-drawer {
      position: fixed;
      inset: 72px 16px auto 16px;
      z-index: 50;
      background: white;
      border: 1px solid var(--border);
      border-radius: 16px;
      box-shadow: var(--shadow);
      padding: 12px;
      display: none;
      flex-direction: column;
      gap: 4px;
    }

    .mobile-drawer.open {
      display: flex;
    }

    .mobile-drawer a {
      padding: 12px 14px;
      border-radius: 10px;
      color: #344054;
      font-weight: 600;
    }

    .mobile-drawer a:hover {
      background: #f4f6f8;
    }

    .mobile-menu-btn {
      display: none;
      border: 1px solid var(--border);
      background: white;
      border-radius: 10px;
      min-width: 44px;
      min-height: 44px;
      font-size: 1.25rem;
    }

    .features-section,
    .trust-section {
      padding: 88px 0 64px;
    }

    .section-header {
      max-width: 900px;
      margin: 0 auto;
      text-align: center;
    }

    .section-header h2 {
      margin: 0;
      font-size: clamp(2rem, 4vw, 3.6rem);
      line-height: 1.1;
      font-weight: 800;
      letter-spacing: -0.03em;
      color: #14213d;
    }

    .section-header p {
      margin: 16px auto 0;
      max-width: 820px;
      color: #6a7895;
      font-size: clamp(1rem, 1.6vw, 1.28rem);
    }

    .trust-grid,
    .features-grid {
      display: grid;
      gap: 22px;
      margin-top: 34px;
    }

    .trust-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .features-grid {
      grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .trust-card,
    .feature-card {
      background: #fbfbfc;
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 26px 24px;
      text-align: center;
      box-shadow: 0 2px 8px rgba(16, 24, 40, 0.03);
    }

    .feature-icon {
      width: 48px;
      height: 48px;
      margin: 0 auto 18px;
      border-radius: 10px;
      background: #eef2ec;
      display: grid;
      place-items: center;
      font-size: 1.35rem;
    }

    .trust-card h3,
    .feature-card h3 {
      margin: 0 0 10px;
      font-size: 1.05rem;
      font-weight: 800;
      color: #14213d;
    }

    .trust-card p,
    .feature-card p {
      margin: 0;
      color: #6b7a96;
      font-size: 0.98rem;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 24px;
      margin-top: 64px;
    }

    .stat-item {
      text-align: center;
    }

    .stat-value {
      color: var(--green);
      font-size: clamp(2rem, 3.4vw, 3.3rem);
      line-height: 1;
      font-weight: 800;
      margin-bottom: 10px;
    }

    .cta-section {
      padding: 56px 0 48px;
      background: #f4f5f4;
      border-top: 1px solid #eceef0;
    }

    .cta-inner {
      text-align: center;
      max-width: 860px;
    }

    .cta-inner h2 {
      margin: 0;
      font-size: clamp(2rem, 4vw, 3.6rem);
      line-height: 1.12;
      color: #14213d;
    }

    .cta-inner p {
      margin: 14px auto 28px;
      max-width: 720px;
      color: #6a7895;
      font-size: clamp(1rem, 1.5vw, 1.26rem);
    }

    .cta-button {
      max-width: 385px;
      margin: 0 auto;
    }

    .site-footer {
      background: #fafafa;
      border-top: 1px solid #dfe3e8;
      padding: 28px 0 34px;
    }

    .footer-inner {
      text-align: center;
    }

    .footer-brand {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      font-weight: 800;
      font-size: 1.05rem;
      color: #14213d;
      margin-bottom: 12px;
    }

    .footer-logo {
      width: 24px;
      height: 24px;
    }

    .site-footer p {
      margin: 0;
      color: #667085;
      font-size: 0.96rem;
    }

    .apply-hero {
      background: var(--green);
      color: white;
      text-align: center;
      padding: 18px 16px 26px;
      border-top: 4px solid #1d4f91;
    }

    .apply-logo {
      width: 88px;
      height: 58px;
      margin: 0 auto 12px;
      background: white;
      border-radius: 2px;
      color: #64748b;
      display: grid;
      place-items: center;
      font-size: 0.6rem;
      font-weight: 800;
      line-height: 1.1;
      text-align: center;
    }

    .apply-hero h1 {
      margin: 0;
      font-size: clamp(2rem, 4vw, 3rem);
      line-height: 1.05;
      font-weight: 800;
    }

    .apply-hero p {
      margin: 6px 0 0;
      font-size: 0.98rem;
      color: rgba(255,255,255,0.92);
    }

    .apply-hero-tags {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 22px;
      margin-top: 8px;
      color: rgba(255,255,255,0.96);
      font-weight: 500;
      font-size: 0.95rem;
    }

    .preloader-screen {
      position: fixed;
      inset: 0;
      z-index: 120;
      background: #f3f4f4;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .preloader-card {
      width: min(100%, 360px);
      background: #fff;
      border: 1px solid #d9dde3;
      border-radius: 8px;
      padding: 24px 22px 30px;
      text-align: center;
      box-shadow: 0 8px 30px rgba(15, 23, 42, 0.05);
    }

    .preloader-icon {
      width: 52px;
      height: 52px;
      border-radius: 999px;
      background: #eef5eb;
      color: var(--green);
      display: grid;
      place-items: center;
      margin: 0 auto 14px;
      font-size: 1.25rem;
    }

    .preloader-card h2 {
      margin: 0;
      font-size: 1.05rem;
      line-height: 1.25;
      color: #14213d;
    }

    .preloader-card p {
      margin: 10px auto 0;
      color: #6b7a96;
      font-size: 0.92rem;
      max-width: 260px;
    }

    .preloader-track {
      width: 100%;
      height: 7px;
      background: #eceef2;
      border-radius: 999px;
      overflow: hidden;
      margin-top: 18px;
    }

    .preloader-fill {
      width: 0%;
      height: 100%;
      background: var(--green);
      border-radius: inherit;
      transition: width 0.12s linear;
    }

    .preloader-percent {
      margin-top: 8px;
      font-size: 0.8rem;
      color: #6b7a96;
    }

    .preloader-status {
      margin-top: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      color: #374151;
      font-weight: 500;
    }

    .preloader-subtext {
      margin-top: 6px;
      font-size: 0.84rem;
      color: #7b8aa5;
    }

    .preloader-tags {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 14px;
      margin-top: 22px;
      font-size: 0.82rem;
      color: #5f6f8e;
    }

    .preloader-dots {
      display: flex;
      justify-content: center;
      gap: 6px;
      margin-top: 18px;
    }

    .preloader-dots span {
      width: 7px;
      height: 7px;
      border-radius: 999px;
      background: #7faa73;
      animation: dotBlink 1.2s infinite ease-in-out;
    }

    .preloader-dots span:nth-child(2) { animation-delay: 0.15s; }
    .preloader-dots span:nth-child(3) { animation-delay: 0.3s; }

    @keyframes dotBlink {
      0%, 80%, 100% { opacity: 0.35; transform: translateY(0); }
      40% { opacity: 1; transform: translateY(-2px); }
    }

    .apply-shell {
      padding: 26px 16px 60px;
      background: #f3f4f4;
    }

    .apply-container {
      width: min(100%, 560px);
      margin: 0 auto;
    }

    .apply-topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      margin-bottom: 14px;
    }

    .apply-topbar h2 {
      margin: 0;
      font-size: 0.98rem;
      color: #111827;
      font-weight: 700;
    }

    .progress-pill {
      padding: 6px 12px;
      border-radius: 999px;
      background: #ececec;
      color: #111827;
      font-size: 0.78rem;
      font-weight: 700;
      white-space: nowrap;
    }

    .progress-track {
      width: 100%;
      height: 6px;
      border-radius: 999px;
      background: #e6e7ea;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      width: 33%;
      background: var(--green);
      border-radius: inherit;
      transition: width 0.25s ease;
    }

    .steps-nav {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
      margin: 18px 0 24px;
      text-align: center;
    }

    .step-item {
      opacity: 0.6;
    }

    .step-item.active,
    .step-item.done {
      opacity: 1;
    }

    .step-icon {
      width: 38px;
      height: 38px;
      border-radius: 999px;
      display: grid;
      place-items: center;
      background: #eceff1;
      margin: 0 auto 8px;
      font-size: 0.95rem;
    }

    .step-item.active .step-icon,
    .step-item.done .step-icon {
      background: var(--green);
      color: white;
    }

    .step-item h3 {
      margin: 0;
      font-size: 0.86rem;
      color: #44608f;
      font-weight: 500;
    }

    .step-item p {
      margin: 2px 0 0;
      font-size: 0.78rem;
      color: #6b7a96;
      line-height: 1.35;
    }

    .form-card {
      background: white;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 18px;
    }

    .form-title {
      font-size: 0.95rem;
      font-weight: 800;
      margin-bottom: 18px;
      color: #14213d;
    }

    .loan-subtitle {
      margin: -2px 0 14px;
      font-size: 0.9rem;
      font-weight: 500;
      color: #111827;
    }

    .opportunity-list {
      display: grid;
      gap: 10px;
      margin-bottom: 8px;
    }

    .opportunity-card {
      position: relative;
      display: flex;
      align-items: center;
      gap: 12px;
      min-height: 58px;
      padding: 12px 14px;
      border: 1px solid #d9dde3;
      border-radius: 8px;
      background: #fff;
      cursor: pointer;
      transition: 0.2s ease;
    }

    .opportunity-card input {
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }

    .opportunity-card.selected {
      border: 2px solid var(--green);
      background: #f6fbf4;
      box-shadow: inset 0 0 0 1px rgba(77, 139, 63, 0.08);
    }

    .opportunity-icon {
      width: 28px;
      height: 28px;
      border-radius: 6px;
      background: #f1f3f5;
      display: grid;
      place-items: center;
      flex-shrink: 0;
      font-size: 0.82rem;
    }

    .selected-icon {
      background: var(--green);
      color: white;
    }

    .opportunity-copy {
      display: flex;
      flex-direction: column;
      gap: 3px;
      min-width: 0;
    }

    .opportunity-copy strong {
      font-size: 0.92rem;
      color: #111827;
      font-weight: 500;
    }

    .opportunity-copy small {
      font-size: 0.76rem;
      color: #6b7a96;
    }

    .opportunity-check {
      margin-left: auto;
      width: 22px;
      height: 22px;
      border-radius: 999px;
      border: 1.5px solid var(--green);
      color: var(--green);
      display: grid;
      place-items: center;
      font-weight: 800;
      font-size: 0.78rem;
      flex-shrink: 0;
    }

    .field-group {
      margin-bottom: 18px;
    }

    .field-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 700;
      color: #111827;
    }

    .field-group input,
    .field-group select {
      width: 100%;
      min-height: 44px;
      padding: 0 14px;
      border-radius: 8px;
      border: 1px solid #d1d5db;
      background: #fff;
      font-size: 0.98rem;
      color: #111827;
    }

    .field-group small {
      display: block;
      margin-top: 8px;
      color: #7b8aa5;
      font-size: 0.85rem;
    }

    .form-actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      border-top: 1px solid #e5e7eb;
      padding-top: 20px;
      margin-top: 18px;
    }

    .ghost-btn,
    .primary-btn {
      min-height: 32px;
      padding: 0 18px;
      border-radius: 4px;
      font-size: 0.86rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      border: 1px solid transparent;
      cursor: pointer;
    }

    .ghost-btn {
      background: #fff;
      color: #111827;
      border-color: #d1d5db;
      min-width: 96px;
    }

    .primary-btn {
      background: var(--green);
      color: #fff;
      border-color: var(--green);
      min-width: 150px;
    }

    .primary-btn:hover {
      background: var(--green-dark);
      border-color: var(--green-dark);
    }

    @media (max-width: 1100px) {
      .features-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 900px) {
      .nav-links,
      .nav-cta {
        display: none;
      }

      .mobile-menu-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
      }

      .brand {
        font-size: 1.3rem;
      }

      .trust-grid {
        grid-template-columns: 1fr;
      }

      .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .features-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .apply-topbar {
        flex-direction: column;
        align-items: flex-start;
      }

      .steps-nav {
        grid-template-columns: 1fr;
        gap: 12px;
      }

      .step-item {
        display: flex;
        align-items: center;
        text-align: left;
        gap: 12px;
      }

      .step-icon {
        margin: 0;
        flex-shrink: 0;
      }

      .form-actions {
        flex-direction: column;
      }

      .ghost-btn,
      .primary-btn {
        width: 100%;
      }
    }

    @media (max-width: 640px) {
      .container {
        width: min(100% - 20px, var(--max));
      }

      .mobile-drawer {
        inset: 64px 10px auto 10px;
      }

      .apply-hero {
        padding: 18px 14px 24px;
      }

      .apply-shell {
        padding: 22px 10px 44px;
      }

      .form-card {
        padding: 18px 14px;
      }

      .nav-inner {
        min-height: 64px;
      }

      .hero {
        padding-top: 36px;
      }

      .hero-badge {
        font-size: 0.82rem;
        padding: 7px 12px;
      }

      .spotlight-card {
        padding: 32px 16px 24px;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="mobile-drawer">
    <a href="#features">Features</a>
    <a href="#trust">Why Trust Us</a>
    <a href="#contact">Contact</a>
    <a href="#apply" data-open-apply>Apply Now</a>
  </div>

  <div id="home-page">
    <header class="navbar">
      <div class="container nav-inner">
        <a href="#" class="brand" data-go-home>
          <span class="brand-logo"></span>
          <span>Nyota Limit Boost</span>
          <span class="country-pill">KE</span>
        </a>

        <nav class="nav-links">
          <a href="#features">Features</a>
          <a href="#trust">Why Trust Us</a>
          <a href="#contact">Contact</a>
        </nav>

        <a href="#apply" class="nav-cta" data-open-apply>Apply Now</a>
        <button class="mobile-menu-btn" aria-label="Open menu">☰</button>
      </div>
    </header>

    <main>
      <section class="hero">
        <div class="container">
          <div class="hero-badge">🇰🇪 National Youth Opportunities Towards Advancement</div>

          <h1>
            Kuza, Imarisha,<br />
            <span class="green">Endeleza</span> <span class="red">Vijana!</span>
          </h1>

          <h2>Empowering Kenya's Future Leaders</h2>

          <p>
            Vijana wa Kenya, fursa ziko hapa! Access youth-focused loans from
            <strong>Ksh. 10,000 to 100,000</strong>
            disbursed instantly to your M-Pesa account.
          </p>

          <p class="hero-note">
            For students, entrepreneurs, and young professionals aged 18-35.
          </p>

          <div class="hero-actions">
            <a href="#apply" class="hero-primary" data-open-apply>Apply for Youth Loan ⚡</a>
            <a href="#learn-more" class="hero-secondary">Learn More</a>
          </div>

          <div class="spotlight-card">
            <div class="spotlight-logo">NYOTA<br>LIMIT BOOST</div>
            <h3>National Youth Opportunities Towards Advancement</h3>
            <p>Kuza, Imarisha, Endeleza Vijana!</p>
            <div class="tags">
              <span class="tag">🇰🇪 Proudly Kenyan</span>
              <span class="tag">🧑🏾‍🎓 Youth Focused</span>
              <span class="tag">📱 M-Pesa Native</span>
            </div>
          </div>
        </div>
      </section>

      <section id="trust" class="trust-section">
        <div class="container">
          <div class="section-header">
            <h2>Trusted by Kenya's Youth</h2>
            <p>
              Nyota Limit Boost is more than just loans. We're a licensed, regulated financial services
              provider committed to empowering Kenya's youth with accessible opportunities.
            </p>
          </div>

          <div class="trust-grid">
            <article class="trust-card">
              <h3>CBK Licensed</h3>
              <p>Licensed money lender under Central Bank of Kenya regulations</p>
            </article>

            <article class="trust-card">
              <h3>Youth Focused</h3>
              <p>Programs designed specifically for Kenya's young population aged 18-35</p>
            </article>

            <article class="trust-card">
              <h3>Registered Company</h3>
              <p>Incorporated in Kenya with physical offices in Nairobi</p>
            </article>
          </div>

          <div class="stats-grid">
            <article class="stat-item">
              <div class="stat-value">100,000+</div>
              <h4>Youth Empowered</h4>
              <p>Across all 47 counties</p>
            </article>

            <article class="stat-item">
              <div class="stat-value">KSh 5B+</div>
              <h4>Disbursed</h4>
              <p>Since launch</p>
            </article>

            <article class="stat-item">
              <div class="stat-value">4.9</div>
              <h4>Customer Rating</h4>
              <p>Google Play Store</p>
            </article>

            <article class="stat-item">
              <div class="stat-value">2 min</div>
              <h4>Average Approval</h4>
              <p>Record fast processing</p>
            </article>
          </div>
        </div>
      </section>

      <section id="features" class="features-section">
        <div class="container">
          <div class="section-header">
            <h2>Why Kenyan Youth Choose Nyota Limit Boost</h2>
            <p>
              Built specifically for Kenya's youth with features that matter most - speed, affordability,
              transparency, and empowerment.
            </p>
          </div>

          <div class="features-grid">
            <article class="feature-card">
              <div class="feature-icon">🎓</div>
              <h3>Youth Education Loans</h3>
              <p>Invest in your future with affordable education financing for students and young professionals</p>
            </article>

            <article class="feature-card">
              <div class="feature-icon">💼</div>
              <h3>Youth Business Loans</h3>
              <p>Start or grow your business with loans designed specifically for young entrepreneurs</p>
            </article>

            <article class="feature-card">
              <div class="feature-icon">📱</div>
              <h3>100% Mobile Experience</h3>
              <p>Designed for Kenyan youth - works perfectly on any smartphone</p>
            </article>

            <article class="feature-card">
              <div class="feature-icon">✔</div>
              <h3>Full Transparency</h3>
              <p>All costs shown upfront - no surprises or hidden charges</p>
            </article>
          </div>
        </div>
      </section>

      <section id="apply" class="cta-section">
        <div class="container cta-inner">
          <h2>Ready to Unlock Your Potential?</h2>
          <p>
            Join the youth movement with Nyota Limit Boost. Apply now and get funds in
            your M-Pesa within minutes.
          </p>
          <a href="#apply" class="hero-primary cta-button" data-open-apply>Apply Now ↗</a>
        </div>
      </section>
    </main>

    <footer class="site-footer">
      <div class="container footer-inner">
        <div class="footer-brand">
          <span class="brand-logo footer-logo"></span>
          <span>Nyota Limit Boost</span>
        </div>
        <p>
          Licensed by Central Bank of Kenya | National Youth Opportunities Towards Advancement |
          Proudly Kenyan
        </p>
      </div>
    </footer>
  </div>

  <div id="application-page" style="display:none;">
    <div id="preloader-screen" class="preloader-screen">
      <div class="preloader-card">
        <div class="preloader-icon">🧮</div>
        <h2>Processing Your Application</h2>
        <p class="preloader-greeting">Hello Applicant, we're calculating your loan eligibility...</p>
        <div class="preloader-track">
          <div class="preloader-fill"></div>
        </div>
        <div class="preloader-percent">0% Complete</div>
        <div class="preloader-status">✅ <span class="preloader-status-text">Analyzing your profile</span></div>
        <div class="preloader-subtext">Preparing your personalized loan offer</div>
        <div class="preloader-tags">
          <span>⚡ Instant</span>
          <span>🛡 Secure</span>
          <span>💰 Affordable</span>
        </div>
        <div class="preloader-dots"><span></span><span></span><span></span></div>
      </div>
    </div>

    <section class="apply-hero">
      <div class="apply-logo">NYOTA<br>LIMIT BOOST</div>
      <h1>Nyota Limit Boost Application</h1>
      <p>National Youth Opportunities Towards Advancement</p>
      <div class="apply-hero-tags">
        <span>⚡ Fast</span>
        <span>🛡 Secure</span>
        <span>☆ Youth Empowerment</span>
      </div>
    </section>

    <section class="apply-shell">
      <div class="apply-container">
        <div class="apply-topbar">
          <h2>Step <span id="step-number">1</span> of 3</h2>
          <div class="progress-pill">33% Complete</div>
        </div>

        <div class="progress-track">
          <div class="progress-fill"></div>
        </div>

        <div class="steps-nav">
          <div class="step-item active">
            <div class="step-icon">👤</div>
            <h3>Personal Info</h3>
            <p>Basic identification details</p>
          </div>
          <div class="step-item">
            <div class="step-icon">💳</div>
            <h3>Loan Details</h3>
            <p>Loan type and preferences</p>
          </div>
          <div class="step-item">
            <div class="step-icon">💼</div>
            <h3>Background</h3>
            <p>Education and employment</p>
          </div>
        </div>

        <form id="checkoutForm" class="form-card" action="/api/loan-offer.php" method="POST">
          <div class="form-step" data-step="1">
            <div class="form-title">👤 Personal Info</div>

            <div class="field-group">
              <label>Full Names</label>
              <input type="text" name="full_name" value="" required />
            </div>

            <div class="field-group">
              <label>M-Pesa Number</label>
              <input type="tel" name="mpesa_number" value="" required />
            </div>

            <div class="field-group">
              <label>National ID Number</label>
              <input type="text" name="national_id" value="" required />
              <small>This will be used for loan verification</small>
            </div>

            <div class="form-actions">
              <button type="button" class="ghost-btn" disabled>← Previous</button>
              <button type="button" class="primary-btn" data-next>Next →</button>
            </div>
          </div>

          <div class="form-step" data-step="2" style="display:none;">
            <div class="form-title">💳 Loan Details</div>
            <div class="loan-subtitle">Choose Your Youth Opportunity</div>

            <div class="opportunity-list">
              <label class="opportunity-card">
                <input type="radio" name="opportunity" value="Startup Loan" />
                <span class="opportunity-icon">🚀</span>
                <span class="opportunity-copy">
                  <strong>Startup Loan</strong>
                  <small>Launch your business dream</small>
                </span>
              </label>

              <label class="opportunity-card">
                <input type="radio" name="opportunity" value="Education Financing" />
                <span class="opportunity-icon">🎓</span>
                <span class="opportunity-copy">
                  <strong>Education Financing</strong>
                  <small>Invest in your future</small>
                </span>
              </label>

              <label class="opportunity-card">
                <input type="radio" name="opportunity" value="Skills Training Loan" />
                <span class="opportunity-icon">💻</span>
                <span class="opportunity-copy">
                  <strong>Skills Training Loan</strong>
                  <small>Professional courses &amp; certifications</small>
                </span>
              </label>

              <label class="opportunity-card">
                <input type="radio" name="opportunity" value="Youth Business Loan" />
                <span class="opportunity-icon">🏢</span>
                <span class="opportunity-copy">
                  <strong>Youth Business Loan</strong>
                  <small>Grow your enterprise</small>
                </span>
              </label>

              <label class="opportunity-card selected">
                <input type="radio" name="opportunity" value="Personal Development" checked />
                <span class="opportunity-icon selected-icon">👤</span>
                <span class="opportunity-copy">
                  <strong>Personal Development</strong>
                  <small>General empowerment</small>
                </span>
                <span class="opportunity-check">✓</span>
              </label>

              <label class="opportunity-card">
                <input type="radio" name="opportunity" value="Emergency Support" />
                <span class="opportunity-icon">⚡</span>
                <span class="opportunity-copy">
                  <strong>Emergency Support</strong>
                  <small>Urgent financial needs</small>
                </span>
              </label>
            </div>

            <div class="form-actions">
              <button type="button" class="ghost-btn" data-prev>← Previous</button>
              <button type="button" class="primary-btn" data-next>Next →</button>
            </div>
          </div>

          <div class="form-step" data-step="3" style="display:none;">
            <div class="form-title">💼 Background</div>

            <div class="field-group">
              <label>Education Level</label>
              <select name="education_level" required>
                <option value="College / University">College / University</option>
                <option value="Secondary">Secondary</option>
                <option value="Postgraduate">Postgraduate</option>
              </select>
            </div>

            <div class="field-group">
              <label>Employment Status</label>
              <select name="employment_status" required>
                <option value="Self-employed">Self-employed</option>
                <option value="Employed">Employed</option>
                <option value="Student">Student</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="field-group">
              <label>Monthly Income</label>
              <select name="monthly_income" required>
                <option value="0-10,000 KES">0-10,000 KES</option>
                <option value="10,000-25,000 KES">10,000-25,000 KES</option>
                <option value="25,000-35,000 KES">25,000-35,000 KES</option>
                <option value="35,000-45,000 KES">35,000-45,000 KES</option>
                <option value="50,000 KES +">50,000 KES +</option>
              </select>
            </div>

            <div class="form-actions">
              <button type="button" class="ghost-btn" data-prev>← Previous</button>
              <button type="submit" class="primary-btn" id="submit-application">Submit Application</button>
            </div>
          </div>
        </form>
      </div>
    </section>
  </div>
</body>
</html>
