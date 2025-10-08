

// Variables globales
let currentUser = null;
let users = [];
let loans = [];
let isFirebaseReady = false;

// Función para inicializar la aplicación después de cargar Firebase
window.initializeApp = async function() {
    try {
        console.log('🔥 Inicializando Firebase...');
        
        // Esperar un momento para asegurar que Firebase esté completamente cargado
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Crear usuario admin por defecto si no existe
        await createDefaultAdmin();
        
        // Cargar datos desde Firebase
        await loadUsersFromFirebase();
        await loadLoansFromFirebase();
        
        // Cargar usuario actual desde localStorage (para mantener sesión)
        const savedUser = localStorage.getItem('currentUser');
        if (savedUser) {
            const userData = JSON.parse(savedUser);
            // Verificar que el usuario aún existe en Firebase
            currentUser = users.find(u => u.id === userData.id) || null;
            if (!currentUser) {
                localStorage.removeItem('currentUser');
            }
        }
        
        isFirebaseReady = true;
        updateNavigation();
        
        console.log('✅ Firebase inicializado correctamente');
        console.log('👥 Usuarios cargados:', users.length);
        
        // Configurar listeners en tiempo real
        setupRealtimeListeners();
        
    } catch (error) {
        console.error('❌ Error inicializando Firebase:', error);
        showNotification('Error de conexión. Por favor verifica tu conexión a internet e intenta nuevamente.', 'error', 'Error de Conexión');
        return;
    }
};

// Función para cargar usuarios desde Firebase
async function loadUsersFromFirebase() {
    try {
        if (!window.firebaseDB) {
            throw new Error('Firebase no está disponible');
        }
        
        const { database, ref, get } = window.firebaseDB;
        const usersRef = ref(database, 'users');
        const snapshot = await get(usersRef);
        
        if (snapshot.exists()) {
            const usersData = snapshot.val();
            users = Object.keys(usersData).map(key => ({
                id: key,
                ...usersData[key]
            }));
        } else {
            users = [];
        }
        console.log('👥 Usuarios cargados desde Firebase:', users.length);
    } catch (error) {
        console.error('Error cargando usuarios desde Firebase:', error);
        users = [];
        throw error;
    }
}

// Función para cargar préstamos desde Firebase
async function loadLoansFromFirebase() {
    try {
        if (!window.firebaseDB) {
            throw new Error('Firebase no está disponible');
        }
        
        const { database, ref, get } = window.firebaseDB;
        const loansRef = ref(database, 'loans');
        const snapshot = await get(loansRef);
        
        if (snapshot.exists()) {
            const loansData = snapshot.val();
            loans = Object.keys(loansData).map(key => ({
                id: key,
                ...loansData[key]
            }));
        } else {
            loans = [];
        }
        console.log('💰 Préstamos cargados desde Firebase:', loans.length);
    } catch (error) {
        console.error('Error cargando préstamos desde Firebase:', error);
        loans = [];
        throw error;
    }
}

// Función para guardar usuario en Firebase
async function saveUserToFirebase(user) {
    try {
        const { database, ref, set } = window.firebaseDB;
        const userRef = ref(database, `users/${user.id}`);
        const { id, ...userData } = user;
        await set(userRef, userData);
        console.log('✅ Usuario guardado en Firebase');
        return true;
    } catch (error) {
        console.error('❌ Error guardando usuario:', error);
        throw error;
    }
}

// Función para guardar préstamo en Firebase
async function saveLoanToFirebase(loan) {
    try {
        const { database, ref, set } = window.firebaseDB;
        const loanRef = ref(database, `loans/${loan.id}`);
        const { id, ...loanData } = loan;
        await set(loanRef, loanData);
        console.log('✅ Préstamo guardado en Firebase');
        return true;
    } catch (error) {
        console.error('❌ Error guardando préstamo:', error);
        throw error;
    }
}

// Función para actualizar préstamo en Firebase
async function updateLoanInFirebase(loanId, updates) {
    try {
        const { database, ref, update } = window.firebaseDB;
        const loanRef = ref(database, `loans/${loanId}`);
        await update(loanRef, updates);
        console.log('✅ Préstamo actualizado en Firebase');
        return true;
    } catch (error) {
        console.error('❌ Error actualizando préstamo:', error);
        throw error;
    }
}

// Función para configurar listeners en tiempo real
function setupRealtimeListeners() {
    const { database, ref, onValue } = window.firebaseDB;
    
    // Listener para usuarios
    const usersRef = ref(database, 'users');
    onValue(usersRef, (snapshot) => {
        if (snapshot.exists()) {
            const usersData = snapshot.val();
            users = Object.keys(usersData).map(key => ({
                id: key,
                ...usersData[key]
            }));
            
            // Actualizar vistas si es necesario
            if (currentUser && currentUser.role === 'admin') {
                loadAdminStats();
            }
        }
    });
    
    // Listener para préstamos
    const loansRef = ref(database, 'loans');
    onValue(loansRef, (snapshot) => {
        if (snapshot.exists()) {
            const loansData = snapshot.val();
            loans = Object.keys(loansData).map(key => ({
                id: key,
                ...loansData[key]
            }));
            
            // Actualizar vistas si es necesario
            if (currentUser) {
                if (currentUser.role === 'admin') {
                    loadAdminStats();
                    loadPendingLoans();
                } else {
                    loadUserLoans();
                }
            }
        }
    });
    
    console.log('🔄 Listeners en tiempo real configurados');
}

// Función para crear admin por defecto
async function createDefaultAdmin() {
    if (!users.find(u => u.role === 'admin')) {
        const adminUser = {
            id: generateId(),
            name: 'Administrador',
            email: 'admin@jaipuru.com',
            phone: '0981123456',
            document: '1234567',
            password: 'admin123',
            role: 'admin',
            createdAt: new Date().toISOString()
        };
        
        users.push(adminUser);
        
        try {
            await saveUserToFirebase(adminUser);
            console.log('👑 Usuario admin creado en Firebase');
        } catch (error) {
            console.error('Error creando admin:', error);
            throw error;
        }
    }
}

// Funciones globales para los onclick del HTML
function showLoginModal() {
    document.getElementById('loginModal').classList.remove('hidden');
}

function showRegisterModal() {
    document.getElementById('registerModal').classList.remove('hidden');
}

function logout() {
    currentUser = null;
    localStorage.removeItem('currentUser');
    showWelcomeSection();
    updateNavigation();
}

// Función para mostrar la sección de bienvenida
function showWelcomeSection() {
    document.getElementById('welcomeSection').classList.remove('hidden');
    document.getElementById('userDashboard').classList.add('hidden');
    document.getElementById('adminDashboard').classList.add('hidden');
}

// Función para mostrar el dashboard del usuario
function showUserDashboard() {
    document.getElementById('welcomeSection').classList.add('hidden');
    document.getElementById('userDashboard').classList.remove('hidden');
    document.getElementById('adminDashboard').classList.add('hidden');
    loadUserProfile();
    loadUserLoans();
    updateLoanCalculator();
}

// Función para mostrar el dashboard del admin
function showAdminDashboard() {
    document.getElementById('welcomeSection').classList.add('hidden');
    document.getElementById('userDashboard').classList.add('hidden');
    document.getElementById('adminDashboard').classList.remove('hidden');
    loadAdminStats();
    loadPendingLoans();
}

// Función para actualizar la navegación
function updateNavigation() {
    const loginBtn = document.getElementById('loginBtn');
    const registerBtn = document.getElementById('registerBtn');
    const logoutBtn = document.getElementById('logoutBtn');
    const userWelcome = document.getElementById('userWelcome');
    const userName = document.getElementById('userName');

    if (currentUser) {
        loginBtn.classList.add('hidden');
        registerBtn.classList.add('hidden');
        logoutBtn.classList.remove('hidden');
        userWelcome.classList.remove('hidden');
        userName.textContent = currentUser.name;

        if (currentUser.role === 'admin') {
            showAdminDashboard();
        } else {
            showUserDashboard();
        }
    } else {
        loginBtn.classList.remove('hidden');
        registerBtn.classList.remove('hidden');
        logoutBtn.classList.add('hidden');
        userWelcome.classList.add('hidden');
        showWelcomeSection();
    }
}

// Función para cargar el perfil del usuario
function loadUserProfile() {
    if (currentUser) {
        document.getElementById('profileName').textContent = currentUser.name;
        document.getElementById('profileEmail').textContent = currentUser.email;
    }
}

// Función para cargar los préstamos del usuario
function loadUserLoans() {
    const userLoans = loans.filter(loan => loan.userId === currentUser.id);
    const activeLoans = userLoans.filter(loan => loan.status === 'approved');
    const totalDebt = activeLoans.reduce((sum, loan) => sum + (loan.remainingBalance || loan.totalAmount), 0);

    document.getElementById('activeLoans').textContent = activeLoans.length;
    document.getElementById('totalDebt').textContent = formatCurrency(totalDebt);

    // Próximo pago
    const nextPaymentDate = activeLoans.length > 0 ? 
        new Date(Math.min(...activeLoans.map(loan => new Date(loan.nextPaymentDate || loan.createdAt)))).toLocaleDateString() : '-';
    document.getElementById('nextPayment').textContent = nextPaymentDate;

    // Historial de préstamos
    loadLoanHistory(userLoans);
}

// Función para cargar el historial de préstamos
function loadLoanHistory(userLoans) {
    const historyContainer = document.getElementById('loanHistory');
    
    if (userLoans.length === 0) {
        historyContainer.innerHTML = '<p class="text-gray-400 text-center py-8">No tienes préstamos registrados aún</p>';
        return;
    }

    historyContainer.innerHTML = userLoans.map(loan => `
        <div class="loan-card bg-slate-800 p-4 rounded-lg border border-slate-600">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h4 class="text-lg font-bold text-white">₲${formatNumber(loan.amount)}</h4>
                    <p class="text-sm text-gray-400">${loan.purpose}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(loan.status)}">
                    ${getStatusText(loan.status)}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400">Fecha de solicitud</p>
                    <p class="text-white">${new Date(loan.createdAt).toLocaleDateString()}</p>
                </div>
                <div>
                    <p class="text-gray-400">Plazo</p>
                    <p class="text-white">${loan.term} semanas</p>
                </div>
                ${loan.status === 'approved' ? `
                    <div>
                        <p class="text-gray-400">Saldo restante</p>
                        <p class="text-white">₲${formatNumber(loan.remainingBalance || loan.totalAmount)}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Próximo pago</p>
                        <p class="text-white">${new Date(loan.nextPaymentDate || loan.createdAt).toLocaleDateString()}</p>
                    </div>
                ` : ''}
            </div>
        </div>
    `).join('');
}

// Función para cargar estadísticas del admin
function loadAdminStats() {
    const pendingLoans = loans.filter(loan => loan.status === 'pending').length;
    const activeLoans = loans.filter(loan => loan.status === 'approved' && (loan.remainingBalance || loan.totalAmount) > 0).length;
    const totalUsers = users.length;
    
    // Calcular ingresos del mes
    const currentMonth = new Date().getMonth();
    const currentYear = new Date().getFullYear();
    const monthlyRevenue = loans
        .filter(loan => {
            const loanDate = new Date(loan.createdAt);
            return loan.status === 'completed' && 
                   loanDate.getMonth() === currentMonth && 
                   loanDate.getFullYear() === currentYear;
        })
        .reduce((sum, loan) => sum + (loan.totalAmount - loan.amount), 0);

    document.getElementById('pendingLoans').textContent = pendingLoans;
    document.getElementById('adminActiveLoans').textContent = activeLoans;
    document.getElementById('totalUsers').textContent = totalUsers;
    document.getElementById('monthlyRevenue').textContent = formatCurrency(monthlyRevenue);
}

// Función para cargar préstamos pendientes
function loadPendingLoans() {
    const pendingLoans = loans.filter(loan => loan.status === 'pending');
    const container = document.getElementById('pendingLoansList');

    if (pendingLoans.length === 0) {
        container.innerHTML = '<p class="text-gray-400 text-center py-8">No hay solicitudes pendientes</p>';
        return;
    }

    container.innerHTML = pendingLoans.map(loan => {
        const user = users.find(u => u.id === loan.userId);
        return `
            <div class="bg-slate-800 p-4 rounded-lg border border-slate-600">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="text-lg font-bold text-white">${user ? user.name : 'Usuario desconocido'}</h4>
                        <p class="text-sm text-gray-400">${user ? user.email : ''}</p>
                        <p class="text-sm text-gray-400">Tel: ${user ? user.phone : ''}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-orange-400">₲${formatNumber(loan.amount)}</p>
                        <p class="text-sm text-gray-400">${loan.term} semanas</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <p class="text-gray-400">Propósito</p>
                        <p class="text-white">${loan.purpose}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Fecha de solicitud</p>
                        <p class="text-white">${new Date(loan.createdAt).toLocaleDateString()}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Total a pagar</p>
                        <p class="text-white">₲${formatNumber(loan.totalAmount)}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Cuota semanal</p>
                        <p class="text-white">₲${formatNumber(loan.weeklyPayment)}</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="approveLoan('${loan.id}')" class="flex-1 bg-green-600 text-white py-2 rounded-lg text-sm hover:bg-green-700 transition">
                        Aprobar
                    </button>
                    <button onclick="rejectLoan('${loan.id}')" class="flex-1 bg-red-600 text-white py-2 rounded-lg text-sm hover:bg-red-700 transition">
                        Rechazar
                    </button>
                    <a href="https://wa.me/${user ? user.phone.replace(/\D/g, '') : ''}" target="_blank" class="flex-1 bg-green-500 text-white py-2 rounded-lg text-sm hover:bg-green-600 transition text-center">
                        WhatsApp
                    </a>
                </div>
            </div>
        `;
    }).join('');
}

// Función para aprobar préstamo
async function approveLoan(loanId) {
    const loanIndex = loans.findIndex(loan => loan.id === loanId);
    if (loanIndex !== -1) {
        const updates = {
            status: 'approved',
            approvedAt: new Date().toISOString(),
            remainingBalance: loans[loanIndex].totalAmount,
            nextPaymentDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString() // 7 días
        };
        
        // Mostrar loading
        document.getElementById('loadingSpinner').classList.remove('hidden');
        
        try {
            const success = await updateLoanInFirebase(loanId, updates);
            
            if (success) {
                // Actualizar localmente
                Object.assign(loans[loanIndex], updates);
                showNotification('Préstamo aprobado exitosamente', 'success', '¡Éxito!');
            } else {
                showNotification('Error al aprobar préstamo. Intenta nuevamente.', 'error', 'Error');
            }
        } catch (error) {
            console.error('Error aprobando préstamo:', error);
            showNotification('Error al aprobar préstamo. Intenta nuevamente.', 'error', 'Error');
        } finally {
            document.getElementById('loadingSpinner').classList.add('hidden');
        }
    }
}

// Función para rechazar préstamo
async function rejectLoan(loanId) {
    const loanIndex = loans.findIndex(loan => loan.id === loanId);
    if (loanIndex !== -1) {
        const updates = {
            status: 'rejected',
            rejectedAt: new Date().toISOString()
        };
        
        // Mostrar loading
        document.getElementById('loadingSpinner').classList.remove('hidden');
        
        try {
            const success = await updateLoanInFirebase(loanId, updates);
            
            if (success) {
                // Actualizar localmente
                Object.assign(loans[loanIndex], updates);
                showNotification('Préstamo rechazado', 'warning', 'Préstamo Rechazado');
            } else {
                showNotification('Error al rechazar préstamo. Intenta nuevamente.', 'error', 'Error');
            }
        } catch (error) {
            console.error('Error rechazando préstamo:', error);
            showNotification('Error al rechazar préstamo. Intenta nuevamente.', 'error', 'Error');
        } finally {
            document.getElementById('loadingSpinner').classList.add('hidden');
        }
    }
}

// Función para actualizar la calculadora de préstamos
function updateLoanCalculator() {
    const amount = parseInt(document.getElementById('loanAmount').value) || 0;
    const term = parseInt(document.getElementById('loanTerm').value) || 1;
    
    const interestRate = 0.30; // 30%
    const totalAmount = amount * (1 + interestRate);
    const weeklyPayment = totalAmount / term;
    const totalInterest = totalAmount - amount;

    document.getElementById('weeklyPayment').textContent = formatCurrency(weeklyPayment);
    document.getElementById('totalPayment').textContent = formatCurrency(totalAmount);
    document.getElementById('totalInterest').textContent = formatCurrency(totalInterest);
}

// Función para mostrar notificaciones
function showNotification(message, type = 'success', title = '') {
    const modal = document.getElementById('notificationModal');
    const icon = document.getElementById('notificationIcon');
    const titleEl = document.getElementById('notificationTitle');
    const messageEl = document.getElementById('notificationMessage');
    const closeBtn = document.getElementById('closeNotificationModal');
    
    // Configurar icono y colores según el tipo
    switch (type) {
        case 'success':
            icon.textContent = '✅';
            closeBtn.className = 'w-full py-2 rounded-lg font-medium transition bg-green-600 text-white hover:bg-green-700';
            titleEl.textContent = title || '¡Éxito!';
            break;
        case 'error':
            icon.textContent = '❌';
            closeBtn.className = 'w-full py-2 rounded-lg font-medium transition bg-red-600 text-white hover:bg-red-700';
            titleEl.textContent = title || 'Error';
            break;
        case 'warning':
            icon.textContent = '⚠️';
            closeBtn.className = 'w-full py-2 rounded-lg font-medium transition bg-orange-600 text-white hover:bg-orange-700';
            titleEl.textContent = title || 'Advertencia';
            break;
        default:
            icon.textContent = 'ℹ️';
            closeBtn.className = 'w-full py-2 rounded-lg font-medium transition bg-blue-600 text-white hover:bg-blue-700';
            titleEl.textContent = title || 'Información';
    }
    
    messageEl.textContent = message;
    modal.classList.remove('hidden');
}

// Funciones de utilidad
function formatCurrency(amount) {
    return `₲${formatNumber(amount)}`;
}

function formatNumber(number) {
    return Math.round(number).toLocaleString('es-PY');
}

function getStatusColor(status) {
    switch (status) {
        case 'pending': return 'bg-orange-600 text-orange-100';
        case 'approved': return 'bg-green-600 text-green-100';
        case 'rejected': return 'bg-red-600 text-red-100';
        case 'completed': return 'bg-blue-600 text-blue-100';
        default: return 'bg-gray-600 text-gray-100';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'pending': return 'Pendiente';
        case 'approved': return 'Aprobado';
        case 'rejected': return 'Rechazado';
        case 'completed': return 'Completado';
        default: return 'Desconocido';
    }
}

function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Event listeners para modales
    document.getElementById('closeLoginModal').addEventListener('click', function() {
        document.getElementById('loginModal').classList.add('hidden');
    });

    document.getElementById('closeRegisterModal').addEventListener('click', function() {
        document.getElementById('registerModal').classList.add('hidden');
    });

    document.getElementById('switchToRegister').addEventListener('click', function() {
        document.getElementById('loginModal').classList.add('hidden');
        document.getElementById('registerModal').classList.remove('hidden');
    });

    document.getElementById('switchToLogin').addEventListener('click', function() {
        document.getElementById('registerModal').classList.add('hidden');
        document.getElementById('loginModal').classList.remove('hidden');
    });

    // Event listener para el formulario de login
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Mostrar loading
        document.getElementById('loadingSpinner').classList.remove('hidden');
        
        try {
            // Verificar que Firebase esté listo
            if (!isFirebaseReady) {
                showNotification('Sistema inicializando, por favor espera un momento...', 'warning', 'Cargando...');
                return;
            }
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            console.log('🔍 Intentando login con:', email);
            console.log('👥 Usuarios disponibles:', users.length);

            // Buscar usuario
            const user = users.find(u => u.email === email && u.password === password);
            
            if (user) {
                currentUser = user;
                localStorage.setItem('currentUser', JSON.stringify(user));
                document.getElementById('loginModal').classList.add('hidden');
                updateNavigation();
                showNotification(`¡Bienvenido ${user.name}!`, 'success', '¡Hola!');
            } else {
                console.log('❌ Usuario no encontrado o contraseña incorrecta');
                
                // Mostrar información de debug
                const emailExists = users.find(u => u.email === email);
                if (emailExists) {
                    console.log('📧 Email encontrado, pero contraseña incorrecta');
                    showNotification('Contraseña incorrecta', 'error', 'Error de Login');
                } else {
                    console.log('📧 Email no encontrado');
                    showNotification('Email no registrado', 'error', 'Error de Login');
                }
            }
        } catch (error) {
            console.error('Error en login:', error);
            showNotification('Error al iniciar sesión. Intenta nuevamente.', 'error', 'Error de Sistema');
        } finally {
            document.getElementById('loadingSpinner').classList.add('hidden');
        }
    });

    // Event listener para el formulario de registro
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!isFirebaseReady) {
            showNotification('Sistema inicializando, por favor espera un momento...', 'warning', 'Cargando...');
            return;
        }
        
        const name = document.getElementById('registerName').value;
        const email = document.getElementById('registerEmail').value;
        const phone = document.getElementById('registerPhone').value;
        const documentValue = document.getElementById('registerDocument').value;
        const password = document.getElementById('registerPassword').value;

        // Verificar si el email ya existe
        if (users.find(u => u.email === email)) {
            showNotification('Este email ya está registrado', 'error', 'Email Duplicado');
            return;
        }

        const newUser = {
            id: generateId(),
            name,
            email,
            phone,
            document: documentValue,
            password,
            role: 'usuario',
            createdAt: new Date().toISOString()
        };

        // Mostrar loading
        document.getElementById('loadingSpinner').classList.remove('hidden');

        try {
            // Guardar en Firebase
            const success = await saveUserToFirebase(newUser);
            
            if (success) {
                users.push(newUser);
                currentUser = newUser;
                localStorage.setItem('currentUser', JSON.stringify(newUser));
                
                document.getElementById('registerModal').classList.add('hidden');
                updateNavigation();
                showNotification('¡Registro exitoso! Bienvenido a JAIPURU PLATA', 'success', '¡Bienvenido!');
            } else {
                showNotification('Error al registrar usuario. Intenta nuevamente.', 'error', 'Error de Registro');
            }
        } catch (error) {
            console.error('Error en registro:', error);
            showNotification('Error al registrar usuario. Verifica tu conexión a internet.', 'error', 'Error de Registro');
        } finally {
            document.getElementById('loadingSpinner').classList.add('hidden');
        }
    });

    // Event listener para el formulario de préstamo
    document.getElementById('loanForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!currentUser) {
            showNotification('Debes iniciar sesión para solicitar un préstamo', 'warning', 'Login Requerido');
            return;
        }

        if (!isFirebaseReady) {
            showNotification('Sistema inicializando, por favor espera un momento...', 'warning', 'Cargando...');
            return;
        }

        const amount = parseInt(document.getElementById('loanAmount').value);
        const term = parseInt(document.getElementById('loanTerm').value);
        const purpose = document.getElementById('loanPurpose').value;
        const acceptTerms = document.getElementById('acceptTerms').checked;

        if (!acceptTerms) {
            showNotification('Debes aceptar los términos y condiciones', 'warning', 'Términos Requeridos');
            return;
        }

        const interestRate = 0.30; // 30%
        const totalAmount = amount * (1 + interestRate);
        const weeklyPayment = totalAmount / term;

        const newLoan = {
            id: generateId(),
            userId: currentUser.id,
            amount,
            term,
            purpose,
            totalAmount,
            weeklyPayment,
            status: 'pending',
            createdAt: new Date().toISOString()
        };

        // Mostrar loading
        document.getElementById('loadingSpinner').classList.remove('hidden');

        try {
            // Guardar en Firebase
            const success = await saveLoanToFirebase(newLoan);
            
            if (success) {
                loans.push(newLoan);
                showNotification('¡Solicitud de préstamo enviada! Te contactaremos pronto.', 'success', '¡Solicitud Enviada!');
                loadUserLoans();
                
                // Limpiar formulario
                document.getElementById('loanForm').reset();
                document.getElementById('loanAmount').value = '1000000';
                document.getElementById('loanTerm').value = '2';
                updateLoanCalculator();
            } else {
                showNotification('Error al enviar solicitud. Intenta nuevamente.', 'error', 'Error');
            }
        } catch (error) {
            console.error('Error creando préstamo:', error);
            showNotification('Error al enviar solicitud. Verifica tu conexión a internet.', 'error', 'Error');
        } finally {
            document.getElementById('loadingSpinner').classList.add('hidden');
        }
    });

    // Event listeners para calculadora de préstamos
    document.getElementById('loanAmount').addEventListener('input', updateLoanCalculator);
    document.getElementById('loanTerm').addEventListener('change', updateLoanCalculator);

    // Event listener para cerrar modal de notificación
    document.getElementById('closeNotificationModal').addEventListener('click', function() {
        document.getElementById('notificationModal').classList.add('hidden');
    });
});