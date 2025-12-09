# Hedera Testnet USDC Faucet (tUSDC)

An **unofficial, community-built Hedera testnet USDC faucet** created by an independent developer to solve long-standing reliability issues in existing USDC testnet faucets and developer tools.

This project provides a stable and developer-friendly way to mint **tUSDC** (Testnet USDC) for testing, integrating, and experimenting with USDC flows on Hedera.  
It is **not affiliated with Hedera, Circle, or the HBAR Foundation**.  
The goal is transparency, open collaboration, and helping the community build better applications.

---

## 🚀 Features

### ✅ MetaMask Support
- Connect MetaMask (desktop + mobile in-app browser)
- Auto-switch to Hedera testnet
- Add tUSDC as a display token
- Associate HTS tokens via EVM-compatible ABI

### ✅ tUSDC Faucet
- Request up to **1000 tUSDC per rolling 24-hour window**
- Supports both EVM and Hedera account IDs  
  Examples:  
  - `0xabc123...`  
  - `0.0.12345` (auto-resolved)

### ✅ tUSDC Transfers
Send tUSDC from MetaMask to:
- Standard EVM addresses
- Hedera native account IDs (`0.0.x`) with automatic resolution

### 🔍 Account Resolution
The backend resolves native Hedera IDs using:

```
/api/resolve-account.php
```

### ♿ Accessibility & UX
- Clean, readable, mobile-first layout
- High contrast + colorblind-friendly palette
- Button loading states
- Error handling and form validation
- Works on iOS Safari + MetaMask in-app browser

---

## 🧪 Token Information (Testnet)

| Field | Value |
|-------|-------|
| **Name** | Hedera Testnet USDC |
| **Symbol** | tUSDC |
| **HTS Token ID** | `0.0.7352375` |
| **EVM Address** | `0x0000000000000000000000000000000000703037` |
| **Decimals** | 6 |
| **Chain ID** | `0x128` |
| **RPC** | `https://testnet.hashio.io/api` |

---

## 🌐 Add Hedera Testnet Account to MetaMask

1. Visit  
   https://portal.hedera.com/dashboard  
2. Switch to **Testnet**
3. Create a testnet account (if needed)
4. Reveal your **HEX Encoded Private Key**
5. Import it in MetaMask:  
   - Click your account icon  
   - Select **Import Account**  
   - Choose **Private Key**  
   - Paste HEX key

Your Hedera testnet account will now work in MetaMask.

---

## 🧭 Go Live With Real USDC (Circle USDC on Mainnet)

Once your tUSDC integration works, move to **Circle USDC on Hedera mainnet**.

### 📌 Mainnet Token Info

| Field | Value |
|-------|-------|
| **Network** | Hedera Mainnet |
| **Standard** | HTS |
| **Mainnet Token ID** | `0.0.456858` |
| **Explorer** | https://hashscan.io/mainnet/token/0.0.456858 |

### 🟦 Circle Go-Live Steps

Official link:  
https://www.circle.com/multi-chain-usdc/hedera

1. Apply for **Circle Mint**
2. Create a business account
3. Complete KYC & compliance
4. Deposit USD via wire or ACH
5. Choose **USDC on Hedera**
6. Mint USDC directly to your Hedera mainnet treasury

### Hedera Ecosystem Support
- **CEX** withdrawals: Binance, Coinbase, OKX, etc.
- **DEX** support: SaucerSwap, Pangolin
- **Wallets**: HashPack, Blade, MetaMask (HIP-482 RPC)

---

## 🔌 API Usage Example

Send a faucet request programmatically:

```http
POST /api/
Content-Type: application/json

{
  "address": "0xEvmOrHederaAddress",
  "amount": 1000
}
```

Response includes:
- mint transaction ID
- transfer transaction ID
- HashScan URL
- rolling window restrictions

---

## 👥 Community & Open Development

This faucet is intentionally open-source so that:
- Developers can audit the code
- Anyone can fork or self-host their own faucet
- The community can propose improvements
- Testnet remains accessible even when official tools are unavailable

Source code repository:  
https://github.com/EmmanuelHaggai/hedera-test-usdc-faucet

Pull requests and suggestions are welcome.

---

## ⚠️ Disclaimer

This is an **unofficial community project** and not an official Hedera or Circle product.  
tUSDC is a **testnet-only token with no real-world value**.  
Do **not** use tUSDC in production systems.

For real transactions, always use **Circle USDC on Hedera mainnet (`0.0.456858`)**.

---
