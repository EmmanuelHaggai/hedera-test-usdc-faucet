# Hedera Testnet USDC Faucet (tUSDC)

A modern, fully responsive web faucet for minting **tUSDC** (Testnet USDC) on the **Hedera Hashgraph testnet**.  
It supports **MetaMask**, **Hedera Token Service (HTS)** association, native Account ID resolution, and token transfers.  
Built for developers who want to test USDC-like flows before going live with **Circle USDC on Hedera mainnet**.

---

## 🚀 Features

### ✅ MetaMask Support
- Connect to MetaMask
- Auto-switch to Hedera testnet
- Add tUSDC as an ERC-20 for display
- Associate HTS tokens via EVM-compatible ABI call

### ✅ tUSDC Faucet
- Request up to **1000 tUSDC per day**
- Sends directly to EVM or Hedera native account IDs  
  Examples:
  - `0xabc123...`
  - `0.0.12345` (auto-resolved to EVM address)

### ✅ tUSDC Transfers
Send tUSDC from MetaMask to:
- EVM addresses
- Hedera 0.0.x native IDs (auto-resolved)

### 🔍 Account Resolution
Backend resolves Hedera account IDs using:
/api/resolve-account.php


### ♿ Accessibility & UX
- Large readable text
- High contrast colors
- Color-blind friendly UI
- Button loaders + disabled states
- Mobile responsive

---

## 🧪 Token Information (Testnet)

| Field | Value |
|-------|-------|
| **Name** | Hedera Testnet USDC |
| **Symbol** | tUSDC |
| **HTS Token ID** | `0.0.7352375` |
| **EVM Address** | `0x0000000000000000000000000000000000703037` |
| **Decimals** | 6 |
| **Chain ID** | Hedera Testnet (`0x128`) |
| **RPC** | `https://testnet.hashio.io/api` |

---

## 🌐 Add Hedera Testnet Account to MetaMask

1. Go to:
   https://portal.hedera.com/dashboard  
2. Create or log in to your Hedera testnet account.
3. Copy the **HEX Encoded Private Key** from the dashboard.
4. In MetaMask:
   - Click your account icon  
   - Select **Import Account**
   - Choose **Private Key**
   - Paste the **HEX key**
5. MetaMask will now show your Hedera testnet account.

---

## 🧭 Go Live With Real USDC on Hedera (Circle USDC)

When ready for production, switch from tUSDC to **native Circle USDC (mainnet)**.

### 📌 Mainnet Token Info

| Field | Value |
|-------|-------|
| **Network** | Hedera Mainnet |
| **Token Standard** | HTS |
| **Mainnet Token ID** | `0.0.456858` |
| **HashScan** | https://hashscan.io/mainnet/token/0.0.456858 |

### 🟦 Circle Mint Go-Live Steps

Official link:  
https://www.circle.com/multi-chain-usdc/hedera

1. Visit the Circle Hedera page.
2. Click **Apply for Circle Mint**.
3. Create a business account.
4. Complete KYC / compliance checks.
5. Deposit USD via bank wire to your Circle Mint account.
6. Choose **USDC on Hedera** as your minting target.
7. Mint directly to your Hedera mainnet treasury account.

### Accessible From:
- **CEXs:** Binance, Coinbase, HTX, Bitstamp, Kraken (depending on region)
- **DEXs on Hedera:**  
  - SaucerSwap  
  - Pangolin (Hedera)  
- **Wallets:** HashPack, Blade, MetaMask (via HIP-482)

---

## 🔌 API Usage

Send a faucet request programmatically:

```http
POST /api/
Content-Type: application/json

{
  "address": "0xEvmOrHederaAddress",
  "amount": 1000
}
```

---

## ⚠️ Disclaimer

This faucet is for development and testing only.
Do NOT use tUSDC as real USDC.
For production, always use Circle USDC on mainnet (0.0.456858).


