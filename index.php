<!doctype html>
<html lang="es">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ShopHub - E-commerce con Stripe</title>
  <script src="https://js.stripe.com/v3/"></script>
  <style>
    body {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #0a0e27;
      color: #e2e8f0;
      min-height: 100vh;
    }

    * {
      box-sizing: border-box;
    }

    /* Header */
    .header {
      background: rgba(15, 23, 42, 0.95);
      backdrop-filter: blur(20px);
      box-shadow: 0 4px 20px rgba(99, 102, 241, 0.15);
      position: sticky;
      top: 0;
      z-index: 100;
      border-bottom: 1px solid rgba(99, 102, 241, 0.2);
    }

    .header-content {
      max-width: 1400px;
      margin: 0 auto;
      padding: 1.5rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 2rem;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 1rem;
      text-decoration: none;
    }

    .logo-icon {
      font-size: 2.5rem;
    }

    .logo-text h1 {
      font-size: 1.8rem;
      margin: 0;
      background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 800;
      letter-spacing: -0.5px;
    }

    .logo-text p {
      font-size: 0.85rem;
      margin: 0;
      color: #94a3b8;
      font-weight: 500;
    }

    .header-actions {
      display: flex;
      gap: 1rem;
      align-items: center;
    }

    .search-bar {
      position: relative;
      width: 400px;
    }

    .search-bar input {
      width: 100%;
      padding: 0.9rem 1.2rem 0.9rem 3rem;
      border: 2px solid rgba(99, 102, 241, 0.3);
      border-radius: 50px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      background: rgba(30, 41, 59, 0.5);
      color: #e2e8f0;
    }

    .search-bar input::placeholder {
      color: #64748b;
    }

    .search-bar input:focus {
      outline: none;
      border-color: #6366f1;
      background: rgba(30, 41, 59, 0.8);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }

    .search-icon {
      position: absolute;
      left: 1.2rem;
      top: 50%;
      transform: translateY(-50%);
      font-size: 1.2rem;
      color: #64748b;
    }

    .cart-button {
      position: relative;
      background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
      color: white;
      border: none;
      padding: 0.9rem 1.8rem;
      border-radius: 50px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
    }

    .cart-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(99, 102, 241, 0.5);
    }

    .cart-badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background: #ef4444;
      color: white;
      border-radius: 50%;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.75rem;
      font-weight: 700;
      border: 2px solid #0a0e27;
    }

    /* Categories */
    .categories-section {
      background: rgba(15, 23, 42, 0.8);
      padding: 2rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .categories-container {
      max-width: 1400px;
      margin: 0 auto;
      display: flex;
      gap: 1rem;
      overflow-x: auto;
      padding: 0.5rem 0;
    }

    .categories-container::-webkit-scrollbar {
      height: 6px;
    }

    .categories-container::-webkit-scrollbar-thumb {
      background: #475569;
      border-radius: 3px;
    }

    .category-chip {
      padding: 0.7rem 1.5rem;
      background: rgba(30, 41, 59, 0.5);
      border: 2px solid rgba(99, 102, 241, 0.3);
      border-radius: 50px;
      cursor: pointer;
      white-space: nowrap;
      font-weight: 600;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      color: #cbd5e1;
    }

    .category-chip:hover {
      background: rgba(30, 41, 59, 0.8);
      border-color: #6366f1;
    }

    .category-chip.active {
      background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
      color: white;
      border-color: transparent;
      box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
    }

    /* Main Content */
    .main-content {
      max-width: 1400px;
      margin: 0 auto;
      padding: 3rem 2rem;
    }

    .section-title {
      font-size: 2rem;
      font-weight: 800;
      margin-bottom: 2rem;
      color: #f1f5f9;
      text-align: center;
      text-shadow: 0 2px 10px rgba(99, 102, 241, 0.3);
    }

    /* Products Grid */
    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 2rem;
      margin-bottom: 3rem;
    }

    .product-card {
      background: rgba(15, 23, 42, 0.9);
      border-radius: 20px;
      overflow: hidden;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
      border: 1px solid rgba(99, 102, 241, 0.2);
      position: relative;
    }

    .product-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 50px rgba(99, 102, 241, 0.3);
      border-color: #6366f1;
    }

    .product-image {
      height: 220px;
      background: linear-gradient(135deg, rgba(30, 41, 59, 0.5) 0%, rgba(15, 23, 42, 0.8) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 5rem;
      position: relative;
      overflow: hidden;
    }

    .product-badge {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: #ef4444;
      color: white;
      padding: 0.4rem 0.8rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
    }

    .product-info {
      padding: 1.5rem;
    }

    .product-category {
      color: #a855f7;
      font-size: 0.8rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.5rem;
    }

    .product-name {
      font-size: 1.3rem;
      font-weight: 700;
      margin: 0 0 0.8rem 0;
      color: #f1f5f9;
      line-height: 1.3;
    }

    .product-description {
      color: #94a3b8;
      font-size: 0.9rem;
      line-height: 1.6;
      margin-bottom: 1rem;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .product-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      padding-top: 1rem;
      border-top: 1px solid rgba(99, 102, 241, 0.2);
    }

    .product-price {
      font-size: 1.8rem;
      font-weight: 800;
      background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .product-stock {
      font-size: 0.8rem;
      color: #10b981;
      font-weight: 600;
    }

    .product-stock.low {
      color: #f59e0b;
    }

    .product-stock.out {
      color: #ef4444;
    }

    .add-to-cart-btn {
      background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
      color: white;
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.9rem;
      box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
      width: 100%;
      margin-top: 0.5rem;
    }

    .add-to-cart-btn:hover:not(:disabled) {
      transform: scale(1.05);
      box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
    }

    .add-to-cart-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    /* Cart Modal */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      backdrop-filter: blur(5px);
    }

    .modal-overlay.active {
      display: flex;
    }

    .modal-content {
      background: #0f172a;
      border-radius: 24px;
      width: 90%;
      max-width: 700px;
      max-height: 85vh;
      overflow-y: auto;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
      animation: modalSlideIn 0.3s ease-out;
      border: 1px solid rgba(99, 102, 241, 0.3);
    }

    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .modal-header {
      padding: 2rem;
      border-bottom: 2px solid rgba(99, 102, 241, 0.3);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
      color: white;
      border-radius: 24px 24px 0 0;
    }

    .modal-header h2 {
      margin: 0;
      font-size: 1.8rem;
      font-weight: 800;
    }

    .modal-close {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      font-size: 2rem;
      cursor: pointer;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .modal-close:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: rotate(90deg);
    }

    .modal-body {
      padding: 2rem;
    }

    .cart-empty {
      text-align: center;
      padding: 3rem 1rem;
      color: #64748b;
    }

    .cart-empty-icon {
      font-size: 5rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }

    .cart-item {
      display: flex;
      gap: 1.5rem;
      padding: 1.5rem;
      border-bottom: 1px solid rgba(99, 102, 241, 0.2);
      align-items: center;
      transition: all 0.3s ease;
    }

    .cart-item:hover {
      background: rgba(30, 41, 59, 0.3);
    }

    .cart-item-image {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, rgba(30, 41, 59, 0.5) 0%, rgba(15, 23, 42, 0.8) 100%);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.5rem;
      flex-shrink: 0;
    }

    .cart-item-details {
      flex: 1;
    }

    .cart-item-name {
      font-weight: 700;
      font-size: 1.1rem;
      color: #f1f5f9;
      margin-bottom: 0.3rem;
    }

    .cart-item-category {
      color: #a855f7;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
    }

    .cart-item-price {
      font-size: 1.2rem;
      font-weight: 700;
      color: #6366f1;
      margin-top: 0.5rem;
    }

    .cart-item-actions {
      display: flex;
      align-items: center;
      gap: 0.8rem;
    }

    .qty-btn {
      background: rgba(30, 41, 59, 0.5);
      border: 1px solid rgba(99, 102, 241, 0.3);
      width: 32px;
      height: 32px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 700;
      font-size: 1.1rem;
      color: #cbd5e1;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .qty-btn:hover {
      background: #6366f1;
      color: white;
      border-color: #6366f1;
    }

    .qty-display {
      font-weight: 700;
      font-size: 1.1rem;
      min-width: 30px;
      text-align: center;
      color: #f1f5f9;
    }

    .remove-btn {
      background: rgba(239, 68, 68, 0.2);
      color: #fca5a5;
      border: 1px solid rgba(239, 68, 68, 0.3);
      padding: 0.5rem 1rem;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.85rem;
      transition: all 0.3s ease;
    }

    .remove-btn:hover {
      background: #ef4444;
      color: white;
      border-color: #ef4444;
    }

    .cart-summary {
      background: linear-gradient(135deg, rgba(30, 41, 59, 0.5) 0%, rgba(15, 23, 42, 0.8) 100%);
      padding: 2rem;
      border-radius: 16px;
      margin-top: 2rem;
      border: 1px solid rgba(99, 102, 241, 0.3);
    }

    .cart-summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 1rem;
      font-size: 1rem;
      color: #cbd5e1;
    }

    .cart-summary-row.total {
      font-size: 1.5rem;
      font-weight: 800;
      color: #6366f1;
      padding-top: 1rem;
      border-top: 2px solid rgba(99, 102, 241, 0.3);
      margin-top: 1rem;
    }

    .checkout-section {
      margin-top: 2rem;
      padding-top: 2rem;
      border-top: 2px solid rgba(99, 102, 241, 0.3);
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #cbd5e1;
      font-size: 0.9rem;
    }

    .form-group input {
      width: 100%;
      padding: 0.9rem;
      border: 2px solid rgba(99, 102, 241, 0.3);
      border-radius: 12px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      background: rgba(30, 41, 59, 0.5);
      color: #f1f5f9;
    }

    .form-group input::placeholder {
      color: #64748b;
    }

    .form-group input:focus {
      outline: none;
      border-color: #6366f1;
      background: rgba(30, 41, 59, 0.8);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }

    .stripe-btn {
      width: 100%;
      background: linear-gradient(135deg, #635bff 0%, #8b5cf6 100%);
      color: white;
      border: none;
      padding: 1.2rem;
      border-radius: 12px;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(99, 91, 255, 0.4);
      margin-top: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .stripe-btn:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(99, 91, 255, 0.5);
    }

    .stripe-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .whatsapp-btn {
      width: 100%;
      background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
      color: white;
      border: none;
      padding: 1.2rem;
      border-radius: 12px;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
      margin-top: 0.8rem;
    }

    .whatsapp-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(37, 211, 102, 0.4);
    }

    /* Footer */
    .footer {
      background: rgba(15, 23, 42, 0.95);
      padding: 3rem 2rem;
      margin-top: 4rem;
      border-top: 1px solid rgba(99, 102, 241, 0.2);
    }

    .footer-content {
      max-width: 1400px;
      margin: 0 auto;
      text-align: center;
    }

    .footer-info {
      margin-bottom: 2rem;
    }

    .footer-contact {
      display: flex;
      justify-content: center;
      gap: 2rem;
      flex-wrap: wrap;
      margin: 1.5rem 0;
    }

    .footer-contact-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: #94a3b8;
      font-weight: 500;
    }

    .footer-text {
      color: #64748b;
      margin-top: 2rem;
      padding-top: 2rem;
      border-top: 1px solid rgba(99, 102, 241, 0.2);
    }

    /* Toast */
    .toast {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      background: #0f172a;
      padding: 1.2rem 1.8rem;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      gap: 1rem;
      z-index: 2000;
      animation: toastSlideIn 0.3s ease-out;
      border-left: 4px solid #10b981;
      border: 1px solid rgba(99, 102, 241, 0.3);
    }

    .toast.error {
      border-left-color: #ef4444;
    }

    .toast-icon {
      font-size: 1.5rem;
    }

    .toast-message {
      font-weight: 600;
      color: #f1f5f9;
    }

    @keyframes toastSlideIn {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    /* Loading */
    .loading-container {
      text-align: center;
      padding: 4rem 2rem;
      color: #cbd5e1;
    }

    .spinner {
      display: inline-block;
      width: 50px;
      height: 50px;
      border: 4px solid rgba(99, 102, 241, 0.3);
      border-top-color: #6366f1;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    /* Payment Methods */
    .payment-methods {
      margin-top: 1.5rem;
      padding: 1.5rem;
      background: rgba(30, 41, 59, 0.5);
      border-radius: 12px;
      border: 1px solid rgba(99, 102, 241, 0.2);
    }

    .payment-methods h4 {
      margin: 0 0 1rem 0;
      color: #cbd5e1;
      font-size: 1rem;
    }

    .payment-logos {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      align-items: center;
      justify-content: center;
    }

    .payment-logo {
      background: rgba(15, 23, 42, 0.8);
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 600;
      color: #94a3b8;
      border: 2px solid rgba(99, 102, 241, 0.2);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .header-content {
        flex-direction: column;
        gap: 1rem;
      }

      .search-bar {
        width: 100%;
      }

      .header-actions {
        width: 100%;
        justify-content: space-between;
      }

      .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
      }

      .cart-item {
        flex-direction: column;
        align-items: flex-start;
      }

      .cart-item-actions {
        width: 100%;
        justify-content: space-between;
      }

      .section-title {
        font-size: 1.5rem;
      }
    }
  </style>
  <style>@view-transition { navigation: auto; }</style>
  <script src="/_sdk/data_sdk.js" type="text/javascript"></script>
  <script src="/_sdk/element_sdk.js" type="text/javascript"></script>
  <script src="https://cdn.tailwindcss.com" type="text/javascript"></script>
 </head>
 <body><!-- Header -->
  <header class="header">
   <div class="header-content"><a href="#" class="logo">
     <div class="logo-icon">
      🛍️
     </div>
     <div class="logo-text">
      <h1>ShopHub</h1>
      <p>Tu tienda online de confianza</p>
     </div></a>
    <div class="header-actions">
     <div class="search-bar"><span class="search-icon">🔍</span> <input type="text" id="search-input" placeholder="Buscar productos...">
     </div><button class="cart-button" id="cart-btn"> 🛒 Carrito <span class="cart-badge" id="cart-badge" style="display: none;">0</span> </button>
    </div>
   </div>
  </header><!-- Categories -->
  <section class="categories-section">
   <div class="categories-container"><button class="category-chip active" data-category="all">🌟 Todos</button> <button class="category-chip" data-category="Electrónica">💻 Electrónica</button> <button class="category-chip" data-category="Ropa">👕 Ropa</button> <button class="category-chip" data-category="Hogar">🏠 Hogar</button> <button class="category-chip" data-category="Deportes">⚽ Deportes</button> <button class="category-chip" data-category="Libros">📚 Libros</button> <button class="category-chip" data-category="Juguetes">🧸 Juguetes</button> <button class="category-chip" data-category="Belleza">💄 Belleza</button>
   </div>
  </section><!-- Main Content -->
  <main class="main-content">
   <h2 class="section-title">Productos Destacados</h2>
   <div id="loading-container" class="loading-container">
    <div class="spinner"></div>
    <p style="margin-top: 1rem;">Cargando productos...</p>
   </div>
   <div class="products-grid" id="products-grid"></div>
  </main><!-- Cart Modal -->
  <div class="modal-overlay" id="cart-modal">
   <div class="modal-content">
    <div class="modal-header">
     <h2>🛒 Tu Carrito</h2><button class="modal-close" id="close-cart">×</button>
    </div>
    <div class="modal-body">
     <div id="cart-items"></div>
     <div id="checkout-section" class="checkout-section" style="display: none;">
      <h3 style="margin-bottom: 1.5rem; color: #cbd5e1;">Información de Contacto</h3>
      <form id="checkout-form">
       <div class="form-group"><label for="customer-name">Nombre Completo *</label> <input type="text" id="customer-name" required placeholder="Juan Pérez">
       </div>
       <div class="form-group"><label for="customer-email">Email *</label> <input type="email" id="customer-email" required placeholder="juan@ejemplo.com">
       </div>
       <div class="form-group"><label for="customer-phone">Teléfono *</label> <input type="tel" id="customer-phone" required placeholder="+595 981 123456">
       </div>
       <div class="payment-methods">
        <h4>💳 Métodos de pago disponibles con Stripe:</h4>
        <div class="payment-logos"><span class="payment-logo">💳 Tarjetas</span> <span class="payment-logo">🍎 Apple Pay</span> <span class="payment-logo">📱 Google Pay</span> <span class="payment-logo">🔐 Seguro</span>
        </div>
       </div><button type="button" class="stripe-btn" id="stripe-btn"> <span id="stripe-text">🔒 Pagar con Stripe</span> <span id="stripe-loading" style="display: none;">⏳ Procesando...</span> </button>
      </form><button type="button" class="whatsapp-btn" id="whatsapp-btn"> 💬 Consultar por WhatsApp </button>
     </div>
    </div>
   </div>
  </div><!-- Footer -->
  <footer class="footer">
   <div class="footer-content">
    <div class="footer-info">
     <h3 style="color: #6366f1; margin-bottom: 1rem;">ShopHub</h3>
     <div class="footer-contact">
      <div class="footer-contact-item">
       📧 contacto@shophub.com
      </div>
      <div class="footer-contact-item">
       📞 +595 981 123456
      </div>
      <div class="footer-contact-item">
       💬 WhatsApp: +595981123456
      </div>
     </div>
    </div>
    <p class="footer-text">© 2024 ShopHub. Todos los derechos reservados. Pagos seguros con Stripe 🔒</p>
   </div>
  </footer>
  <script>
    // Sample products
    const sampleProducts = [
      { id: 'prod-1', name: 'Laptop Gaming Pro', category: 'Electrónica', price: 8500000, stock: 15, icon: '💻', description: 'Laptop potente con procesador i7 y tarjeta gráfica RTX' },
      { id: 'prod-2', name: 'Smartphone X12', category: 'Electrónica', price: 3500000, stock: 25, icon: '📱', description: 'Smartphone con pantalla OLED y cámara de 108MP' },
      { id: 'prod-3', name: 'Auriculares Bluetooth', category: 'Electrónica', price: 450000, stock: 50, icon: '🎧', description: 'Auriculares inalámbricos con cancelación de ruido' },
      { id: 'prod-4', name: 'Camiseta Premium', category: 'Ropa', price: 120000, stock: 100, icon: '👕', description: 'Camiseta de algodón 100% orgánico' },
      { id: 'prod-5', name: 'Zapatillas Deportivas', category: 'Ropa', price: 380000, stock: 60, icon: '👟', description: 'Zapatillas para running con tecnología de amortiguación' },
      { id: 'prod-6', name: 'Sofá Moderno', category: 'Hogar', price: 2800000, stock: 8, icon: '🛋️', description: 'Sofá de 3 plazas con diseño contemporáneo' },
      { id: 'prod-7', name: 'Lámpara LED', category: 'Hogar', price: 180000, stock: 40, icon: '💡', description: 'Lámpara inteligente con control por app' },
      { id: 'prod-8', name: 'Bicicleta Montaña', category: 'Deportes', price: 1900000, stock: 12, icon: '🚴', description: 'Bicicleta de montaña con 21 velocidades' },
      { id: 'prod-9', name: 'Balón Fútbol', category: 'Deportes', price: 150000, stock: 80, icon: '⚽', description: 'Balón oficial tamaño 5' },
      { id: 'prod-10', name: 'Libro Bestseller', category: 'Libros', price: 85000, stock: 150, icon: '📖', description: 'El libro más vendido del año' },
      { id: 'prod-11', name: 'Muñeca Coleccionable', category: 'Juguetes', price: 220000, stock: 45, icon: '🪆', description: 'Muñeca de edición limitada' },
      { id: 'prod-12', name: 'Set de Maquillaje', category: 'Belleza', price: 350000, stock: 30, icon: '💄', description: 'Kit completo de maquillaje profesional' }
    ];

    let cart = [];
    let currentCategory = 'all';
    let searchQuery = '';
    let stripe = null;

    // Configurar Stripe
    const STRIPE_PUBLIC_KEY = 'pk_test_51SoFagCx4RFfYXMMia6DgyoXFZ6GxfPdinJ8bOB9GPKdNlBNAOJPUwymBtoSX8adpOsIyIUOIzgIuUMhMffTZmGS00R8KfwmlU';
    
    if (window.Stripe) {
      stripe = Stripe(STRIPE_PUBLIC_KEY);
    }

    function formatPrice(price) {
      return '₲ ' + price.toLocaleString('es-PY');
    }

    function renderProducts() {
      const container = document.getElementById('products-grid');
      const loadingContainer = document.getElementById('loading-container');
      
      let filtered = sampleProducts;
      
      if (currentCategory !== 'all') {
        filtered = filtered.filter(p => p.category === currentCategory);
      }
      
      if (searchQuery) {
        filtered = filtered.filter(p => 
          p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
          p.description.toLowerCase().includes(searchQuery.toLowerCase()) ||
          p.category.toLowerCase().includes(searchQuery.toLowerCase())
        );
      }
      
      loadingContainer.style.display = 'none';
      
      if (filtered.length === 0) {
        container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #cbd5e1; padding: 3rem;"><h3>No se encontraron productos</h3><p>Intenta con otra búsqueda o categoría</p></div>';
        return;
      }
      
      container.innerHTML = filtered.map(product => {
        const inCart = cart.find(item => item.id === product.id);
        const stockClass = product.stock === 0 ? 'out' : product.stock < 10 ? 'low' : '';
        const stockText = product.stock === 0 ? 'Agotado' : product.stock < 10 ? `Solo ${product.stock}` : `${product.stock} disponibles`;
        
        return `
          <div class="product-card">
            ${product.stock < 10 && product.stock > 0 ? '<div class="product-badge">🔥 Últimas unidades!</div>' : ''}
            ${product.stock === 0 ? '<div class="product-badge" style="background: #64748b;">Agotado</div>' : ''}
            <div class="product-image">
              ${product.icon}
            </div>
            <div class="product-info">
              <div class="product-category">${product.category}</div>
              <h3 class="product-name">${product.name}</h3>
              <p class="product-description">${product.description}</p>
              <div class="product-footer">
                <div>
                  <div class="product-price">${formatPrice(product.price)}</div>
                  <div class="product-stock ${stockClass}">${stockText}</div>
                </div>
              </div>
              <button 
                class="add-to-cart-btn" 
                onclick="addToCart('${product.id}')"
                ${product.stock === 0 ? 'disabled' : ''}
              >
                ${product.stock === 0 ? '❌ Sin stock' : inCart ? '✅ En carrito' : '🛒 Agregar'}
              </button>
            </div>
          </div>
        `;
      }).join('');
    }

    function addToCart(productId) {
      const product = sampleProducts.find(p => p.id === productId);
      if (!product || product.stock === 0) return;
      
      const existing = cart.find(item => item.id === productId);
      if (existing) {
        if (existing.quantity < product.stock) {
          existing.quantity++;
          showToast('Cantidad actualizada en el carrito');
        } else {
          showToast('No hay más stock disponible', 'error');
          return;
        }
      } else {
        cart.push({
          id: product.id,
          name: product.name,
          category: product.category,
          price: product.price,
          icon: product.icon,
          quantity: 1,
          maxStock: product.stock
        });
        showToast('Producto agregado al carrito');
      }
      
      updateCartBadge();
      renderProducts();
    }

    function updateCartBadge() {
      const badge = document.getElementById('cart-badge');
      const total = cart.reduce((sum, item) => sum + item.quantity, 0);
      badge.textContent = total;
      badge.style.display = total > 0 ? 'flex' : 'none';
    }

    function openCart() {
      document.getElementById('cart-modal').classList.add('active');
      renderCart();
    }

    function closeCart() {
      document.getElementById('cart-modal').classList.remove('active');
    }

    function renderCart() {
      const container = document.getElementById('cart-items');
      const checkoutSection = document.getElementById('checkout-section');
      
      if (cart.length === 0) {
        container.innerHTML = `
          <div class="cart-empty">
            <div class="cart-empty-icon">🛒</div>
            <h3>Tu carrito está vacío</h3>
            <p>¡Agrega algunos productos para empezar!</p>
          </div>
        `;
        checkoutSection.style.display = 'none';
        return;
      }
      
      const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
      const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
      
      container.innerHTML = cart.map(item => `
        <div class="cart-item">
          <div class="cart-item-image">${item.icon}</div>
          <div class="cart-item-details">
            <div class="cart-item-name">${item.name}</div>
            <div class="cart-item-category">${item.category}</div>
            <div class="cart-item-price">${formatPrice(item.price)} × ${item.quantity} = ${formatPrice(item.price * item.quantity)}</div>
          </div>
          <div class="cart-item-actions">
            <button class="qty-btn" onclick="decreaseQuantity('${item.id}')">−</button>
            <span class="qty-display">${item.quantity}</span>
            <button class="qty-btn" onclick="increaseQuantity('${item.id}')">+</button>
            <button class="remove-btn" onclick="removeFromCart('${item.id}')">🗑️</button>
          </div>
        </div>
      `).join('') + `
        <div class="cart-summary">
          <div class="cart-summary-row">
            <span>Total de productos:</span>
            <span>${totalItems} unidades</span>
          </div>
          <div class="cart-summary-row total">
            <span>Total a pagar:</span>
            <span>${formatPrice(total)}</span>
          </div>
        </div>
      `;
      
      checkoutSection.style.display = 'block';
    }

    function increaseQuantity(productId) {
      const item = cart.find(i => i.id === productId);
      if (item && item.quantity < item.maxStock) {
        item.quantity++;
        updateCartBadge();
        renderCart();
      } else {
        showToast('No hay más stock disponible', 'error');
      }
    }

    function decreaseQuantity(productId) {
      const item = cart.find(i => i.id === productId);
      if (item) {
        item.quantity--;
        if (item.quantity === 0) {
          removeFromCart(productId);
        } else {
          updateCartBadge();
          renderCart();
        }
      }
    }

    function removeFromCart(productId) {
      cart = cart.filter(item => item.id !== productId);
      updateCartBadge();
      renderCart();
      renderProducts();
      showToast('Producto eliminado del carrito');
    }

    async function processStripePayment() {
      const name = document.getElementById('customer-name').value.trim();
      const email = document.getElementById('customer-email').value.trim();
      const phone = document.getElementById('customer-phone').value.trim();
      
      if (!name || !email || !phone) {
        showToast('Por favor completa todos los campos', 'error');
        return;
      }
      
      if (cart.length === 0) {
        showToast('El carrito está vacío', 'error');
        return;
      }

      if (!stripe) {
        showToast('Stripe no está disponible', 'error');
        return;
      }

      const btn = document.getElementById('stripe-btn');
      btn.disabled = true;
      document.getElementById('stripe-text').style.display = 'none';
      document.getElementById('stripe-loading').style.display = 'inline';

      try {
        const orderId = 'ORD-' + Date.now() + '-' + Math.floor(Math.random() * 10000);
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

        const response = await fetch('/api/create-checkout-session.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            order_id: orderId,
            customer: { name, email, phone },
            items: cart,
            total: total
          })
        });

        const data = await response.json();

        if (!data.id) {
          throw new Error(data.error || 'Error al crear la sesión de pago');
        }

        const result = await stripe.redirectToCheckout({ sessionId: data.id });
        
        if (result.error) {
          throw new Error(result.error.message);
        }

      } catch (error) {
        console.error('Error completo:', error);
        showToast('Error al procesar el pago: ' + error.message, 'error');
        
        btn.disabled = false;
        document.getElementById('stripe-text').style.display = 'inline';
        document.getElementById('stripe-loading').style.display = 'none';
      }
    }

    function contactWhatsApp() {
      if (cart.length === 0) {
        showToast('El carrito está vacío', 'error');
        return;
      }
      
      let message = '¡Hola! Estoy interesado en los siguientes productos:\n\n';
      
      cart.forEach((item, idx) => {
        message += `${idx + 1}. ${item.name}\n`;
        message += `   Categoría: ${item.category}\n`;
        message += `   Precio: ${formatPrice(item.price)}\n`;
        message += `   Cantidad: ${item.quantity}\n`;
        message += `   Subtotal: ${formatPrice(item.price * item.quantity)}\n\n`;
      });
      
      const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
      message += `TOTAL: ${formatPrice(total)}`;
      
      const whatsappNumber = '+595981123456';
      const cleanNumber = whatsappNumber.replace(/[^0-9]/g, '');
      const url = `https://wa.me/${cleanNumber}?text=${encodeURIComponent(message)}`;
      window.open(url, '_blank', 'noopener,noreferrer');
    }

    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.innerHTML = `
        <span class="toast-icon">${type === 'success' ? '✅' : '❌'}</span>
        <span class="toast-message">${message}</span>
      `;
      document.body.appendChild(toast);
      
      setTimeout(() => {
        toast.remove();
      }, 3000);
    }

    function filterByCategory(category) {
      currentCategory = category;
      renderProducts();
      
      document.querySelectorAll('.category-chip').forEach(chip => {
        chip.classList.remove('active');
      });
      event.target.classList.add('active');
    }

    function search(query) {
      searchQuery = query;
      renderProducts();
    }

    document.addEventListener('DOMContentLoaded', () => {
      renderProducts();
      
      document.getElementById('cart-btn').addEventListener('click', openCart);
      document.getElementById('close-cart').addEventListener('click', closeCart);
      document.getElementById('stripe-btn').addEventListener('click', processStripePayment);
      document.getElementById('whatsapp-btn').addEventListener('click', contactWhatsApp);
      document.getElementById('search-input').addEventListener('input', (e) => search(e.target.value));
      
      document.querySelectorAll('.category-chip').forEach(chip => {
        chip.addEventListener('click', () => filterByCategory(chip.dataset.category));
      });
      
      document.getElementById('cart-modal').addEventListener('click', (e) => {
        if (e.target.id === 'cart-modal') closeCart();
      });
    });
  </script>
 <script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9bc1fff941bdf5a4',t:'MTc2ODEwOTE5My4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>