<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hedera Test USDC Faucet</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.jsdelivr.net/npm/ethers@6.7.0/dist/ethers.umd.min.js"></script>
  <style>
    :root {
      --bg: #050816;
      --bg-alt: #0b1020;
      --card: #101828;
      --accent: #3b82f6;
      --accent-soft: rgba(59,130,246,0.15);
      --accent-strong: rgba(59,130,246,0.30);
      --border: #1e293b;
      --text: #f9fafb;
      --muted: #cbd5f5;
      --error: #f97373;
      --success: #4ade80;
      --radius-lg: 18px;
      --radius-sm: 10px;
      --shadow-soft: 0 18px 45px rgba(15,23,42,0.75);
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: radial-gradient(circle at top, #1d293b 0, #020617 45%, #000 100%);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    a {
      color: var(--accent);
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    header {
      border-bottom: 1px solid rgba(148,163,184,0.24);
      background: linear-gradient(135deg, rgba(15,23,42,0.96), rgba(15,23,42,0.94));
      position: sticky;
      top: 0;
      z-index: 30;
      backdrop-filter: blur(14px);
    }

    .nav-inner {
      max-width: 1120px;
      margin: 0 auto;
      padding: 14px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .brand-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo-pill {
      width: 40px;
      height: 40px;
      border-radius: 999px;
      background: radial-gradient(circle at 30% 20%, #38bdf8, #4f46e5);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 12px 30px rgba(56,189,248,0.4);
      font-weight: 700;
      font-size: 20px;
    }

    .brand-text {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .brand-text .title {
      font-size: 20px;
      font-weight: 700;
      letter-spacing: 0.03em;
    }

    .brand-text .subtitle {
      font-size: 13px;
      color: var(--muted);
      opacity: 0.85;
    }

    nav {
      display: flex;
      align-items: center;
      gap: 16px;
      font-size: 14px;
    }

    nav a {
      padding: 6px 10px;
      border-radius: 999px;
    }

    nav a:hover {
      background: rgba(148,163,184,0.1);
      text-decoration: none;
    }

    .nav-cta {
      padding: 8px 16px;
      border-radius: 999px;
      background: var(--accent);
      color: #0b1120;
      font-weight: 600;
      border: none;
      cursor: pointer;
      font-size: 14px;
      box-shadow: 0 12px 30px rgba(59,130,246,0.6);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .nav-cta span.icon {
      font-size: 16px;
    }

    .nav-cta:hover {
      filter: brightness(1.08);
      transform: translateY(-0.5px);
    }

    main {
      flex: 1;
      max-width: 1120px;
      margin: 0 auto;
      padding: 26px 18px 40px;
      display: grid;
      grid-template-columns: minmax(0, 1.75fr) minmax(0, 1.25fr);
      gap: 26px;
    }

    @media (max-width: 960px) {
      main {
        grid-template-columns: minmax(0, 1fr);
      }
      nav { display: none; }
    }

    h1, h2, h3 { margin: 0; }

    .hero-card {
      background: radial-gradient(circle at top left, rgba(59,130,246,0.26), transparent 60%),
                  radial-gradient(circle at bottom right, rgba(16,185,129,0.18), transparent 65%),
                  var(--card);
      border-radius: 26px;
      padding: 22px 22px 20px;
      border: 1px solid rgba(148,163,184,0.3);
      box-shadow: var(--shadow-soft);
      position: relative;
      overflow: hidden;
    }

    .hero-tag {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 5px 11px;
      border-radius: 999px;
      background: rgba(15,23,42,0.7);
      border: 1px solid rgba(148,163,184,0.4);
      font-size: 13px;
      color: var(--muted);
      margin-bottom: 14px;
    }

    .hero-tag span.dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #4ade80;
      box-shadow: 0 0 0 4px rgba(74,222,128,0.3);
    }

    .hero-title {
      font-size: 28px;
      line-height: 1.2;
      margin-bottom: 8px;
    }

    .hero-lead {
      font-size: 16px;
      color: var(--muted);
      max-width: 560px;
      margin-bottom: 18px;
    }

    .hero-meta-row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 14px;
      font-size: 13px;
      color: var(--muted);
    }

    .pill {
      padding: 5px 10px;
      border-radius: 999px;
      border: 1px solid rgba(148,163,184,0.3);
      background: rgba(15,23,42,0.8);
    }

    .hero-hbar-link {
      font-size: 13px;
      color: var(--muted);
      margin-bottom: 8px;
    }

    .hero-hbar-link a { font-weight: 500; }

    .token-box {
      margin-top: 10px;
      padding: 10px 12px;
      border-radius: 14px;
      background: rgba(15,23,42,0.96);
      border: 1px dashed rgba(148,163,184,0.5);
      font-size: 13px;
      color: var(--muted);
    }

    .token-row {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 4px;
    }

    .token-row span.label { opacity: 0.9; }

    .token-row span.value {
      font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 13px;
    }

    .token-row:last-child { margin-bottom: 0; }

    .grid-two {
      margin-top: 18px;
      display: grid;
      grid-template-columns: minmax(0,1.1fr) minmax(0,1fr);
      gap: 14px;
    }

    @media (max-width: 768px) {
      .grid-two { grid-template-columns: minmax(0,1fr); }
    }

    .card {
      background: rgba(15,23,42,0.97);
      border-radius: var(--radius-lg);
      padding: 14px 14px 16px;
      border: 1px solid var(--border);
    }

    .card h3 {
      font-size: 16px;
      margin-bottom: 6px;
    }

    .card p {
      font-size: 14px;
      color: var(--muted);
      margin: 0 0 10px;
    }

    .field-group {
      display: flex;
      flex-direction: column;
      gap: 4px;
      margin-bottom: 10px;
    }

    label {
      font-size: 14px;
      font-weight: 500;
    }

    input[type="text"],
    input[type="number"] {
      width: 100%;
      padding: 10px 11px;
      border-radius: var(--radius-sm);
      border: 1px solid rgba(148,163,184,0.6);
      background: rgba(15,23,42,0.94);
      color: var(--text);
      font-size: 15px;
      font-family: inherit;
      outline: none;
    }

    input::placeholder { color: rgba(148,163,184,0.7); }

    input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 1px var(--accent-soft);
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      border-radius: 999px;
      border: none;
      padding: 9px 16px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.08s ease, box-shadow 0.08s ease, filter 0.08s ease, background 0.08s ease;
      white-space: nowrap;
    }

    .btn-primary {
      background: var(--accent);
      color: #020617;
      box-shadow: 0 14px 30px rgba(59,130,246,0.55);
    }

    .btn-secondary {
      background: rgba(15,23,42,0.9);
      border: 1px solid rgba(148,163,184,0.7);
      color: var(--text);
    }

    .btn-ghost {
      background: rgba(15,23,42,0.75);
      border: 1px solid rgba(148,163,184,0.35);
      color: var(--muted);
      font-weight: 500;
    }

    .btn:hover:not(:disabled) {
      transform: translateY(-1px);
      filter: brightness(1.05);
      box-shadow: 0 18px 34px rgba(15,23,42,0.7);
    }

    .btn:disabled {
      opacity: 0.55;
      cursor: not-allowed;
      box-shadow: none;
      transform: none;
    }

    .btn .spinner {
      width: 16px;
      height: 16px;
      border-radius: 999px;
      border: 2px solid rgba(15,23,42,0.1);
      border-top-color: #020617;
      border-right-color: #020617;
      animation: spin 0.7s linear infinite;
    }

    .btn-secondary .spinner,
    .btn-ghost .spinner {
      border-color: rgba(148,163,184,0.35);
      border-top-color: var(--accent);
      border-right-color: var(--accent);
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    .status-text {
      font-size: 14px;
      margin-top: 6px;
      min-height: 20px;
      color: var(--muted);
    }

    .status-text.error { color: var(--error); }
    .status-text.success { color: var(--success); }

    .status-text strong { font-weight: 600; }

    .sidebar {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .side-card {
      background: rgba(15,23,42,0.97);
      border-radius: 20px;
      border: 1px solid var(--border);
      padding: 14px 14px 16px;
    }

    .side-card h3 {
      font-size: 15px;
      margin-bottom: 6px;
    }

    .side-card p {
      font-size: 14px;
      color: var(--muted);
      margin: 0 0 8px;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 9px;
      border-radius: 999px;
      background: rgba(15,23,42,0.9);
      border: 1px solid rgba(148,163,184,0.7);
      font-size: 12px;
      color: var(--muted);
      margin-bottom: 6px;
    }

    .badge-dot {
      width: 7px;
      height: 7px;
      border-radius: 999px;
      background: #22c55e;
    }

    code, pre {
      font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 13px;
    }

    pre {
      background: #020617;
      border-radius: 12px;
      border: 1px solid rgba(148,163,184,0.4);
      padding: 10px 11px;
      overflow-x: auto;
      color: var(--muted);
      margin: 8px 0;
    }

    .token-pill {
      display: inline-flex;
      flex-wrap: wrap;
      gap: 6px;
      font-size: 13px;
      color: var(--muted);
    }

    .token-pill span.badge-token {
      border-radius: 999px;
      border: 1px solid rgba(148,163,184,0.7);
      padding: 3px 8px;
      background: rgba(15,23,42,0.9);
    }

    footer {
      border-top: 1px solid rgba(148,163,184,0.24);
      padding: 12px 18px 16px;
      font-size: 13px;
      color: rgba(148,163,184,0.85);
      text-align: center;
      background: rgba(2,6,23,0.96);
    }

    footer a { color: var(--muted); }
  </style>
</head>
<body>
  <header>
    <div class="nav-inner">
      <div class="brand-left">
        <div class="logo-pill">H</div>
        <div class="brand-text">
          <div class="title">Hedera Test USDC Faucet</div>
          <div class="subtitle">Mint tUSDC on Hedera testnet with MetaMask</div>
        </div>
      </div>
      <nav>
        <a href="#faucet">Faucet</a>
        <a href="#golive">Go live</a>
        <a href="#docs">Docs</a>
        <a href="#blog">Blog</a>
        <button class="nav-cta" type="button" onclick="document.getElementById('faucet').scrollIntoView({behavior:'smooth'})">
          <span class="icon">⚡</span>
          <span>Get test tUSDC</span>
        </button>
      </nav>
    </div>
  </header>

  <main>
    <section id="faucet">
      <div class="hero-card">
        <div class="hero-tag">
          <span class="dot"></span>
          <span>Hedera testnet · tUSDC faucet</span>
        </div>
        <h1 class="hero-title">Get Hedera Testnet USDC (tUSDC)</h1>
        <p class="hero-lead">
          Mint a test version of USDC on Hedera testnet and send it to your wallet.
          You can request up to <strong>1000 tUSDC per day</strong> per address and use it to
          build and test USDC integrations safely.
        </p>

        <div class="hero-meta-row">
          <div class="pill">Token standard: Hedera Token Service (HTS)</div>
          <div class="pill">Supports MetaMask + native account IDs</div>
        </div>

        <p class="hero-hbar-link">
          Need test HBAR for gas?
          <a href="https://portal.hedera.com/faucet" target="_blank" rel="noopener noreferrer">
            Get HBAR from the official Hedera testnet faucet.
          </a>
        </p>

        <div class="token-box" aria-label="Token details">
          <div class="token-row">
            <span class="label">Token name</span>
            <span class="value">Hedera Testnet USDC</span>
          </div>
          <div class="token-row">
            <span class="label">Symbol</span>
            <span class="value">tUSDC</span>
          </div>
          <div class="token-row">
            <span class="label">Token ID (HTS)</span>
            <span class="value">0.0.7352375</span>
          </div>
          <div class="token-row">
            <span class="label">EVM address</span>
            <span class="value">0x0000000000000000000000000000000000703037</span>
          </div>
          <div class="token-row">
            <span class="label">Decimals</span>
            <span class="value">6</span>
          </div>
        </div>

        <div class="grid-two" style="margin-top:18px;">
          <div class="card" aria-label="MetaMask connection and token association">
            <h3>Step 1 · Connect & import tUSDC</h3>
            <p>
              Connect your MetaMask wallet, switch to Hedera testnet, and add the tUSDC token for display.
              You can then send an on-chain association transaction so your account can receive tUSDC.
            </p>

            <div class="field-group">
              <button id="connect-metamask-btn" class="btn btn-primary" type="button">
                <span>Connect MetaMask</span>
              </button>
              <p id="metamask-status" class="status-text">MetaMask status: Not connected</p>
            </div>

            <div class="field-group">
              <button id="import-token-btn" class="btn btn-secondary" type="button">
                <span>Import tUSDC into MetaMask</span>
              </button>
              <p id="import-status" class="status-text"></p>
            </div>

            <div class="field-group">
              <button id="associate-token-btn" class="btn btn-ghost" type="button">
                <span>Associate tUSDC via MetaMask</span>
              </button>
              <p id="association-status" class="status-text"></p>
            </div>
          </div>

          <div class="card" aria-label="Faucet request">
            <h3>Step 2 · Request tUSDC from faucet</h3>
            <p>
              After your wallet is associated with tUSDC, request up to 1000 tUSDC per day for testing.
            </p>

            <div class="field-group">
              <label for="recipient-input">Recipient address or account ID</label>
              <input
                type="text"
                id="recipient-input"
                placeholder="0xEvmAddress or 0.0.1234"
                autocomplete="off"
              >
            </div>

            <div class="field-group">
              <label for="amount-input">Amount (max 1000 per request)</label>
              <input
                type="number"
                id="amount-input"
                value="1000"
                min="1"
                max="1000"
                step="1"
              >
            </div>

            <button id="request-faucet-btn" class="btn btn-primary" type="button">
              <span>Request tUSDC</span>
            </button>
            <p id="faucet-status" class="status-text"></p>
          </div>
        </div>

        <div class="card" style="margin-top:18px;" aria-label="Send tUSDC">
          <h3>Step 3 · Send tUSDC to other accounts</h3>
          <p>
            Once your wallet is funded, you can send tUSDC from MetaMask either to another EVM address
            or to a Hedera native account ID (for example <code>0.0.7054893</code>).
          </p>
          <p style="font-size:13px;color:var(--muted);margin-bottom:10px;">
            The recipient must also be associated with tUSDC before they can receive the tokens.
          </p>

          <div class="field-group">
            <label for="send-to-input">Recipient (0x... or 0.0.x)</label>
            <input
              type="text"
              id="send-to-input"
              placeholder="0xRecipientAddress or 0.0.7054893"
              autocomplete="off"
            >
          </div>

          <div class="field-group">
            <label for="send-amount-input">Amount of tUSDC to send</label>
            <input
              type="number"
              id="send-amount-input"
              value="10"
              min="0"
              step="0.000001"
            >
          </div>

          <button id="send-token-btn" class="btn btn-secondary" type="button">
            <span>Send tUSDC</span>
          </button>
          <p id="send-status" class="status-text"></p>
        </div>
      </div>
    </section>

    <aside class="sidebar">
       <!-- Docs + Hedera Portal import instructions -->
      <div id="docs" class="side-card">
        <h3>Import your Hedera testnet account into MetaMask</h3>
        <p>
          If you created a testnet account in the Hedera Portal, you can import that same account
          into MetaMask using the Hex Encoded Private Key.
        </p>
        <ol style="padding-left:18px;font-size:14px;color:var(--muted);margin:0 0 8px;">
          <li>
            Go to
            <a href="https://portal.hedera.com/dashboard" target="_blank" rel="noopener noreferrer">
              https://portal.hedera.com/dashboard
            </a>
            and sign in.
          </li>
          <li>
            In the dashboard, switch to <strong>Testnet</strong> and create a testnet account if you do not have one yet.
          </li>
          <li>
            Open that account’s details, go to the keys section, and reveal the
            <strong>Hex Encoded Private Key</strong>. Copy the full hex string.
          </li>
          <li>
            In MetaMask, first make sure you are on <strong>Hedera Testnet</strong> (the
            “Connect MetaMask” button on this page can add and switch the network for you).
          </li>
          <li>
            In MetaMask, open the account menu and choose <strong>Import account</strong>.
          </li>
          <li>
            Select the <strong>Private key</strong> import option and paste the Hex Encoded Private Key
            from the Hedera Portal, then confirm.
          </li>
          <li>
            MetaMask will create an imported account whose EVM address maps to your Hedera testnet account.
            You can now use this faucet and MetaMask with the same account ID you see in the Portal.
          </li>
        </ol>

      </div>


      <!-- Detailed go-live section -->
      <div id="golive" class="side-card">
        <span class="badge">
          <span class="badge-dot"></span>
          <span>Ready for mainnet</span>
        </span>
        <h3>Go live with real USDC on Hedera</h3>
        <p>
          When your integration is stable on testnet with this faucet, you can move to
          <strong>Circle’s native USDC on Hedera mainnet</strong>.
        </p>

        <h4 style="font-size:14px;margin:6px 0 4px;">1. Use the official Circle USDC tokens</h4>
        <ul style="padding-left:18px;font-size:14px;color:var(--muted);margin:0 0 8px;">
          <li>
            <strong>Mainnet USDC (HTS):</strong><br>
            Token ID: <code>0.0.456858</code><br>
            HashScan:
            <a href="https://hashscan.io/mainnet/token/0.0.456858" target="_blank" rel="noopener noreferrer">
              View on HashScan
            </a>
          </li>

        </ul>

        <h4 style="font-size:14px;margin:8px 0 4px;">2. Apply for Circle Mint on Hedera</h4>
        <ul style="padding-left:18px;font-size:14px;color:var(--muted);margin:0 0 8px;">
          <li>
            Visit:
            <a href="https://www.circle.com/multi-chain-usdc/hedera" target="_blank" rel="noopener noreferrer">
              https://www.circle.com/multi-chain-usdc/hedera
            </a>
          </li>
          <li>Create or log into your Circle business account.</li>
          <li>Complete KYC and compliance approval.</li>
          <li>Fund your Circle account with fiat (for example via wire or ACH).</li>
          <li>Select <strong>USDC on Hedera</strong> as the minting network.</li>
          <li>Mint USDC to your Hedera mainnet treasury or smart contracts and distribute from there.</li>
        </ul>

        <h4 style="font-size:14px;margin:8px 0 4px;">3. Use centralized exchanges (CEX) as a funding route</h4>
        <p style="font-size:14px;color:var(--muted);margin:0 0 6px;">
          Many teams also let users move USDC from centralized exchanges to Hedera wallets.
          When exchanges list Hedera as a network for USDC withdrawals, users typically:
        </p>
        <ol style="padding-left:18px;font-size:14px;color:var(--muted);margin:0 0 8px;">
          <li>Open a CEX such as Binance, OKX, KuCoin, Bybit or similar.</li>
          <li>Go to <em>Withdraw → USDC</em>.</li>
          <li>Select the <strong>Hedera</strong> network once it is available in the list.</li>
          <li>Paste their Hedera EVM address from HashPack, Blade, or MetaMask (with Hedera RPC).</li>
          <li>Confirm the withdrawal and wait for on-chain finality.</li>
        </ol>
        <p style="font-size:13px;color:var(--muted);margin:0 0 8px;">
          In your app, you can show the user’s EVM address with a one-click copy button to reduce mistakes.
        </p>

        <h4 style="font-size:14px;margin:8px 0 4px;">4. Connect DeFi and DEX liquidity</h4>
        <p style="font-size:14px;color:var(--muted);margin:0 0 6px;">
          Once users hold USDC on Hedera mainnet, they can interact with the DeFi ecosystem:
        </p>
        <ul style="padding-left:18px;font-size:14px;color:var(--muted);margin:0 0 8px;">
          <li>
            Use Hedera-native DEXs such as SaucerSwap or Heliswap for swaps and liquidity pools
            (for example USDC/HBAR or USDC/other HTS tokens).
          </li>
          <li>
            Provide USDC liquidity to earn trading fees and protocol rewards.
          </li>
          <li>
            Route swaps through DEX contracts directly from your dApp using their ABIs and addresses.
          </li>
        </ul>

        <h4 style="font-size:14px;margin:8px 0 4px;">5. Production checklist</h4>
        <ul style="padding-left:18px;font-size:14px;color:var(--muted);margin:0 0 8px;">
          <li>Update token IDs from this demo tUSDC (<code>0.0.7352375</code>) to the official Circle IDs.</li>
          <li>Use dedicated production Hedera operator and treasury accounts with secure key storage.</li>
          <li>Remove any free minting logic on mainnet. Only Circle Mint, CEX deposits, or tightly controlled admin mints should create USDC.</li>
          <li>Apply rate limiting and abuse protection on all production endpoints.</li>
          <li>Audit contracts, signing flows, and logging around USDC transfers and swaps.</li>
        </ul>

        <p style="font-size:13px;color:var(--muted);margin-top:4px;">
          This faucet is for <strong>testnet only</strong>. Testnet tUSDC has no real-world value. For real value,
          always use the official mainnet USDC token <code>0.0.456858</code> and follow Circle’s compliance
          and exchange guidelines.
        </p>

        <button
          type="button"
          class="btn btn-ghost"
          onclick="window.open('https://www.circle.com/multi-chain-usdc/hedera','_blank','noopener')"
        >
          <span>Open Circle Hedera page</span>
        </button>
      </div>

  


    </aside>
  </main>

  <footer>
    Hedera Test USDC Faucet · For development only · When you are ready for production,
    use native Circle USDC on Hedera (<code>0.0.456858</code>) via Circle Mint.
  </footer>

  <script>
    const HEDERA_TESTNET_CHAIN_ID = "0x128";
    const HEDERA_RPC_URL = "https://testnet.hashio.io/api";
    const HEDERA_EXPLORER_URL = "https://hashscan.io/testnet";

    const TOKEN_ADDRESS = "0x0000000000000000000000000000000000703037";
    const TOKEN_SYMBOL = "tUSDC";
    const TOKEN_DECIMALS = 6;

    const IMPORT_FLAG_KEY =
      "tusdc_imported_" + HEDERA_TESTNET_CHAIN_ID + "_" + TOKEN_ADDRESS.toLowerCase();

    const connectBtn = document.getElementById("connect-metamask-btn");
    const metamaskStatus = document.getElementById("metamask-status");
    const recipientInput = document.getElementById("recipient-input");
    const amountInput = document.getElementById("amount-input");
    const requestBtn = document.getElementById("request-faucet-btn");
    const faucetStatus = document.getElementById("faucet-status");

    const importBtn = document.getElementById("import-token-btn");
    const importStatus = document.getElementById("import-status");

    const associateBtn = document.getElementById("associate-token-btn");
    const associationStatus = document.getElementById("association-status");

    const sendBtn = document.getElementById("send-token-btn");
    const sendToInput = document.getElementById("send-to-input");
    const sendAmountInput = document.getElementById("send-amount-input");
    const sendStatus = document.getElementById("send-status");

    let connectedAddress = null;

    function setButtonLoading(button, loading, labelWhenIdle) {
      if (!button) return;
      button.disabled = loading;
      if (loading) {
        button.dataset.label = labelWhenIdle || button.textContent.trim();
        button.innerHTML = '<span class="spinner" aria-hidden="true"></span><span>Working...</span>';
      } else {
        const original = button.dataset.label || labelWhenIdle || button.textContent.trim();
        button.innerHTML = "<span>" + original + "</span>";
      }
    }

    async function ensureHederaNetwork() {
      if (!window.ethereum) {
        throw new Error("MetaMask not detected.");
      }

      try {
        await window.ethereum.request({
          method: "wallet_switchEthereumChain",
          params: [{ chainId: HEDERA_TESTNET_CHAIN_ID }]
        });
      } catch (switchError) {
        if (switchError.code === 4902) {
          await window.ethereum.request({
            method: "wallet_addEthereumChain",
            params: [{
              chainId: HEDERA_TESTNET_CHAIN_ID,
              rpcUrls: [HEDERA_RPC_URL],
              chainName: "Hedera Testnet",
              nativeCurrency: {
                name: "HBAR",
                symbol: "HBAR",
                decimals: 18
              },
              blockExplorerUrls: [HEDERA_EXPLORER_URL]
            }]
          });
        } else {
          throw switchError;
        }
      }
    }

    async function connectMetaMask() {
      if (!window.ethereum) {
        metamaskStatus.textContent = "MetaMask status: Not detected. Please install MetaMask.";
        metamaskStatus.className = "status-text error";
        return;
      }

      setButtonLoading(connectBtn, true, "Connect MetaMask");
      metamaskStatus.textContent = "Connecting to MetaMask...";
      metamaskStatus.className = "status-text";

      try {
        await ensureHederaNetwork();

        const accounts = await window.ethereum.request({
          method: "eth_requestAccounts"
        });

        if (!accounts || accounts.length === 0) {
          metamaskStatus.textContent = "MetaMask status: No accounts found.";
          metamaskStatus.className = "status-text error";
          return;
        }

        connectedAddress = accounts[0];
        metamaskStatus.textContent = "MetaMask status: Connected as " + connectedAddress;
        metamaskStatus.className = "status-text success";
        recipientInput.value = connectedAddress;

        const chainId = await window.ethereum.request({ method: "eth_chainId" });
        console.log("Connected chainId:", chainId);
      } catch (err) {
        console.error(err);
        metamaskStatus.textContent = "MetaMask status: Error - " + (err.message || err);
        metamaskStatus.className = "status-text error";
      } finally {
        setButtonLoading(connectBtn, false, "Connect MetaMask");
      }
    }

    async function addTokenToMetaMask() {
      if (!window.ethereum) {
        importStatus.textContent = "MetaMask not detected.";
        importStatus.className = "status-text error";
        return false;
      }

      if (localStorage.getItem(IMPORT_FLAG_KEY) === "1") {
        importStatus.textContent =
          "tUSDC already imported in this browser for Hedera Testnet.";
        importStatus.className = "status-text success";
        return true;
      }

      setButtonLoading(importBtn, true, "Import tUSDC into MetaMask");
      importStatus.textContent = "Opening MetaMask to add token...";
      importStatus.className = "status-text";

      try {
        const wasAdded = await window.ethereum.request({
          method: "wallet_watchAsset",
          params: {
            type: "ERC20",
            options: {
              address: TOKEN_ADDRESS,
              symbol: TOKEN_SYMBOL,
              decimals: TOKEN_DECIMALS
            }
          }
        });

        if (wasAdded) {
          importStatus.textContent =
            "tUSDC imported into MetaMask (display only). You still need to associate it on Hedera.";
          importStatus.className = "status-text success";
          localStorage.setItem(IMPORT_FLAG_KEY, "1");
          return true;
        } else {
          importStatus.textContent =
            "tUSDC import was rejected or failed in MetaMask.";
          importStatus.className = "status-text error";
          return false;
        }
      } catch (err) {
        console.error(err);
        importStatus.textContent = "Error importing token: " + (err.message || err);
        importStatus.className = "status-text error";
        return false;
      } finally {
        setButtonLoading(importBtn, false, "Import tUSDC into MetaMask");
      }
    }

    async function associateTokenViaMetaMask() {
      if (!window.ethereum) {
        associationStatus.textContent = "MetaMask not detected.";
        associationStatus.className = "status-text error";
        return;
      }

      if (!connectedAddress) {
        associationStatus.textContent = "Connect MetaMask first.";
        associationStatus.className = "status-text error";
        return;
      }

      setButtonLoading(associateBtn, true, "Associate tUSDC via MetaMask");
      associationStatus.textContent = "Preparing association transaction...";
      associationStatus.className = "status-text";

      try {
        await ensureHederaNetwork();

        const provider = new ethers.BrowserProvider(window.ethereum);
        await provider.send("eth_requestAccounts", []);
        const signer = await provider.getSigner();

        const tokenAssociateAbi = ["function associate()"];
        const tokenContract = new ethers.Contract(
          TOKEN_ADDRESS,
          tokenAssociateAbi,
          signer
        );

        const tx = await tokenContract.associate({ gasLimit: 800000 });
        associationStatus.textContent = "Association tx sent: " + tx.hash;
        associationStatus.className = "status-text";

        const receipt = await tx.wait();
        associationStatus.textContent =
          "Association confirmed in block " + receipt.blockNumber + ".";
        associationStatus.className = "status-text success";
      } catch (err) {
        console.error(err);
        associationStatus.textContent =
          "Error associating tUSDC: " + (err.message || err);
        associationStatus.className = "status-text error";
      } finally {
        setButtonLoading(associateBtn, false, "Associate tUSDC via MetaMask");
      }
    }

    async function requestFaucet() {
      const address = recipientInput.value.trim();
      let amount = parseInt(amountInput.value, 10);

      if (!address) {
        faucetStatus.textContent =
          "Please provide a wallet address or Hedera account ID.";
        faucetStatus.className = "status-text error";
        return;
      }

      if (isNaN(amount) || amount <= 0) {
        faucetStatus.textContent = "Please provide a valid amount.";
        faucetStatus.className = "status-text error";
        return;
      }

      if (amount > 1000) {
        amount = 1000;
        amountInput.value = "1000";
      }

      setButtonLoading(requestBtn, true, "Request tUSDC");
      faucetStatus.textContent = "Sending request to faucet...";
      faucetStatus.className = "status-text";

      try {
        const res = await fetch("/api/", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ address, amount })
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
          const errorText = data.error || "Faucet request failed.";
          faucetStatus.textContent = "Error: " + errorText;
          faucetStatus.className = "status-text error";
          return;
        }

        let msg = "Success! " + amount + " tUSDC requested for " + address + ".";
        if (data.txId) {
          msg += " Tx: " + data.txId;
        }
        if (data.explorerUrl) {
          msg += " You can view it here: " + data.explorerUrl;
        }
        faucetStatus.textContent = msg;
        faucetStatus.className = "status-text success";
      } catch (err) {
        console.error(err);
        faucetStatus.textContent =
          "Error calling faucet: " + (err.message || err);
        faucetStatus.className = "status-text error";
      } finally {
        setButtonLoading(requestBtn, false, "Request tUSDC");
      }
    }

    async function resolveNativeToEvm(nativeId) {
      const res = await fetch("/api/resolve-account.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ accountId: nativeId }),
      });

      const text = await res.text();
      let data = {};
      try {
        data = JSON.parse(text);
      } catch (e) {
        console.error("resolve-account JSON parse error:", e, "raw:", text);
        throw new Error("Resolve endpoint returned invalid JSON");
      }

      console.log("resolve-account response:", res.status, data);

      if (!res.ok || !data.ok || !data.evmAddress) {
        throw new Error(data.error || "Failed to resolve Hedera account ID");
      }

      return data.evmAddress;
    }

    function isNativeAccountId(value) {
      return /^\d+\.\d+\.\d+$/.test(value.trim());
    }

    function isEvmAddress(value) {
      return /^0x[0-9a-fA-F]{40}$/.test(value.trim());
    }

    async function sendToken() {
      if (!window.ethereum) {
        sendStatus.textContent = "MetaMask not detected.";
        sendStatus.className = "status-text error";
        return;
      }

      if (!connectedAddress) {
        sendStatus.textContent = "Connect MetaMask first.";
        sendStatus.className = "status-text error";
        return;
      }

      let to = sendToInput.value.trim();
      const amountStr = sendAmountInput.value.trim();

      if (!to) {
        sendStatus.textContent = "Please enter a recipient address or account ID.";
        sendStatus.className = "status-text error";
        return;
      }

      if (!amountStr || isNaN(parseFloat(amountStr)) || parseFloat(amountStr) <= 0) {
        sendStatus.textContent = "Please enter a valid amount.";
        sendStatus.className = "status-text error";
        return;
      }

      setButtonLoading(sendBtn, true, "Send tUSDC");
      sendStatus.textContent = "Preparing transfer transaction...";
      sendStatus.className = "status-text";

      try {
        await ensureHederaNetwork();

        if (isNativeAccountId(to)) {
          sendStatus.textContent = "Resolving Hedera account ID to EVM address...";
          sendStatus.className = "status-text";
          to = await resolveNativeToEvm(to);
        }

        if (!isEvmAddress(to)) {
          sendStatus.textContent =
            "Recipient must be a valid EVM address (0x...) or native 0.0.x account ID.";
          sendStatus.className = "status-text error";
          return;
        }

        const provider = new ethers.BrowserProvider(window.ethereum);
        const signer = await provider.getSigner();

        const erc20Abi = [
          "function transfer(address to, uint256 amount) public returns (bool)"
        ];

        const contract = new ethers.Contract(TOKEN_ADDRESS, erc20Abi, signer);
        const amountWei = ethers.parseUnits(amountStr, TOKEN_DECIMALS);

        const tx = await contract.transfer(to, amountWei);
        sendStatus.textContent = "Transfer tx sent: " + tx.hash;
        sendStatus.className = "status-text";

        const receipt = await tx.wait();
        sendStatus.textContent =
          "Transfer confirmed in block " + receipt.blockNumber + ".";
        sendStatus.className = "status-text success";
      } catch (err) {
        console.error(err);
        sendStatus.textContent =
          "Error sending tUSDC: " + (err.message || err);
        sendStatus.className = "status-text error";
      } finally {
        setButtonLoading(sendBtn, false, "Send tUSDC");
      }
    }

    connectBtn.addEventListener("click", connectMetaMask);
    importBtn.addEventListener("click", addTokenToMetaMask);
    associateBtn.addEventListener("click", associateTokenViaMetaMask);
    requestBtn.addEventListener("click", requestFaucet);
    sendBtn.addEventListener("click", sendToken);
  </script>
</body>
</html>
