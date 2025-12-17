<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hedera Test USDC Faucet</title>
  <script src="https://cdn.jsdelivr.net/npm/ethers@6.7.0/dist/ethers.umd.min.js"></script>
</head>
<body>
  <header>
    <h1>Hedera Test USDC Faucet</h1>
    <nav>
      <a href="#faucet">Faucet</a> |
      <a href="#docs">Docs</a> |
      <a href="#blog">Blog</a>
    </nav>
    <hr>
  </header>

  <main>
    <section id="faucet">
      <h2>Get Test USDC on Hedera</h2>
      <p>
        This faucet mints a test version of USDC on Hedera testnet and sends it to your wallet.
        You can request up to <strong>1000 tUSDC per day</strong> per address.
      </p>

      <p>
        Need test HBAR for gas?
        <a href="https://portal.hedera.com/faucet" target="_blank" rel="noopener noreferrer">
          Get HBAR from the official Hedera testnet faucet
        </a>
      </p>

      <div>
        <p><strong>Token name:</strong> Hedera Testnet USDC</p>
        <p><strong>Symbol:</strong> tUSDC</p>
        <p><strong>Token ID (HTS):</strong> 0.0.7352375</p>
        <p><strong>EVM Address:</strong> 0x0000000000000000000000000000000000703037</p>
        <p><strong>Decimals:</strong> 6</p>
      </div>

      <hr>

      <h3>Connect your wallet</h3>
      <button id="connect-metamask-btn">Connect MetaMask</button>
      <p id="metamask-status">MetaMask status: Not connected</p>

      <h3>Import and associate tUSDC</h3>
      <p>
        Importing tUSDC into MetaMask only makes it visible in your wallet.
        To actually receive the token on Hedera, your account must also <strong>associate</strong> with tUSDC.
      </p>
      <button id="import-token-btn">Import tUSDC into MetaMask (display only)</button>
      <p id="import-status"></p>

      <p>
        After importing, use the button below to send an on-chain association transaction
        from MetaMask. Once confirmed, your account can receive tUSDC.
      </p>
      <button id="associate-token-btn">Associate tUSDC via MetaMask</button>
      <p id="association-status"></p>

      <h3>Recipient address</h3>
      <p>
        This can be a Hedera EVM address (from MetaMask on Hedera testnet)
        or a Hedera account ID like <code>0.0.1234</code> if your backend supports it.
      </p>
      <input
        type="text"
        id="recipient-input"
        placeholder="0xEvmAddress or 0.0.1234"
        style="width: 320px;"
      />
      <br><br>

      <label for="amount-input">Amount of tUSDC (max 1000 per request):</label><br>
      <input
        type="number"
        id="amount-input"
        value="1000"
        min="1"
        max="1000"
        step="1"
      />
      <br><br>




      <button id="request-faucet-btn">Request tUSDC</button>
      <p id="faucet-status"></p>

      <hr>


      <h3>Send tUSDC from MetaMask</h3>
      <p>
        Once your wallet is associated and funded with tUSDC, you can send it to another
        already associated account from here. The recipient can be either:
      </p>
      <ul>
        <li>An EVM address like <code>0x1234...</code>, or</li>
        <li>A Hedera native account ID like <code>0.0.7054893</code></li>
      </ul>

      <label for="send-to-input">Recipient (0x... or 0.0.x):</label><br>
      <input
        type="text"
        id="send-to-input"
        placeholder="0xRecipientAddress or 0.0.7054893"
        style="width: 320px;"
      />
      <br><br>

      <label for="send-amount-input">Amount of tUSDC to send:</label><br>
      <input
        type="number"
        id="send-amount-input"
        value="10"
        min="0"
        step="0.000001"
      />
      <br><br>

      <button id="send-token-btn">Send tUSDC</button>
      <p id="send-status"></p>


      <hr>
    </section>

    <!-- Docs and Blog sections omitted here for brevity, keep your previous ones -->
  </main>

  <script>
    // Hedera Testnet configuration
    const HEDERA_TESTNET_CHAIN_ID = "0x128"; // 296 decimal
    const HEDERA_RPC_URL = "https://testnet.hashio.io/api";
    const HEDERA_EXPLORER_URL = "https://hashscan.io/testnet";

    // tUSDC token
    const TOKEN_ADDRESS = "0x0000000000000000000000000000000000703037";
    const TOKEN_SYMBOL = "tUSDC";
    const TOKEN_DECIMALS = 6;

    // Local flag so we do not spam "Add token" dialog in the same browser
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
        return;
      }

      try {
        await ensureHederaNetwork();

        const accounts = await window.ethereum.request({
          method: "eth_requestAccounts"
        });

        if (!accounts || accounts.length === 0) {
          metamaskStatus.textContent = "MetaMask status: No accounts found.";
          return;
        }

        connectedAddress = accounts[0];
        metamaskStatus.textContent = "MetaMask status: Connected as " + connectedAddress;
        recipientInput.value = connectedAddress;

        const chainId = await window.ethereum.request({ method: "eth_chainId" });
        console.log("Connected chainId:", chainId);
      } catch (err) {
        console.error(err);
        metamaskStatus.textContent = "MetaMask status: Error - " + (err.message || err);
      }
    }

    async function addTokenToMetaMask() {
      if (!window.ethereum) {
        importStatus.textContent = "MetaMask not detected.";
        return false;
      }

      if (localStorage.getItem(IMPORT_FLAG_KEY) === "1") {
        importStatus.textContent =
          "tUSDC already imported in this browser for Hedera Testnet.";
        return true;
      }

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
          localStorage.setItem(IMPORT_FLAG_KEY, "1");
          return true;
        } else {
          importStatus.textContent =
            "tUSDC import was rejected or failed in MetaMask.";
          return false;
        }
      } catch (err) {
        console.error(err);
        importStatus.textContent = "Error importing token: " + (err.message || err);
        return false;
      }
    }

    async function associateTokenViaMetaMask() {
      if (!window.ethereum) {
        associationStatus.textContent = "MetaMask not detected.";
        return;
      }

      if (!connectedAddress) {
        associationStatus.textContent = "Connect MetaMask first.";
        return;
      }

      try {
        associationStatus.textContent = "Preparing association transaction...";
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

        const receipt = await tx.wait();
        associationStatus.textContent =
          "Association confirmed in block " + receipt.blockNumber + ".";
      } catch (err) {
        console.error(err);
        associationStatus.textContent =
          "Error associating tUSDC: " + (err.message || err);
      }
    }

    async function requestFaucet() {
      const address = recipientInput.value.trim();
      let amount = parseInt(amountInput.value, 10);

      if (!address) {
        faucetStatus.textContent =
          "Please provide a wallet address or Hedera account ID.";
        return;
      }

      if (isNaN(amount) || amount <= 0) {
        faucetStatus.textContent = "Please provide a valid amount.";
        return;
      }

      if (amount > 1000) {
        amount = 1000;
        amountInput.value = "1000";
      }

      faucetStatus.textContent = "Sending request to faucet...";

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
      } catch (err) {
        console.error(err);
        faucetStatus.textContent =
          "Error calling faucet: " + (err.message || err);
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
      // Very simple 0.0.x pattern check
      return /^\d+\.\d+\.\d+$/.test(value.trim());
    }

    function isEvmAddress(value) {
      return /^0x[0-9a-fA-F]{40}$/.test(value.trim());
    }

    async function sendToken() {
      if (!window.ethereum) {
        sendStatus.textContent = "MetaMask not detected.";
        return;
      }

      if (!connectedAddress) {
        sendStatus.textContent = "Connect MetaMask first.";
        return;
      }

      let to = sendToInput.value.trim();
      const amountStr = sendAmountInput.value.trim();

      if (!to) {
        sendStatus.textContent = "Please enter a recipient address or account ID.";
        return;
      }

      if (!amountStr || isNaN(parseFloat(amountStr)) || parseFloat(amountStr) <= 0) {
        sendStatus.textContent = "Please enter a valid amount.";
        return;
      }

      try {
        sendStatus.textContent = "Preparing transfer transaction...";
        await ensureHederaNetwork();

        // If the user entered a native account ID, resolve it to an EVM address first
        if (isNativeAccountId(to)) {
          sendStatus.textContent = "Resolving Hedera account ID to EVM address...";
          to = await resolveNativeToEvm(to);
        }

        if (!isEvmAddress(to)) {
          sendStatus.textContent =
            "Recipient must be a valid EVM address (0x...) or native 0.0.x account ID.";
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

        const receipt = await tx.wait();
        sendStatus.textContent =
          "Transfer confirmed in block " + receipt.blockNumber + ".";
      } catch (err) {
        console.error(err);
        sendStatus.textContent =
          "Error sending tUSDC: " + (err.message || err);
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
