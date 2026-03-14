// Authentication Guard - Include in all protected pages
// This script checks if user is authenticated and redirects to login if not

(function() {
  'use strict';
  
  let authChecked = false;
  const currentUser = { email: null, displayName: null, uid: null };
  
  // Check authentication status
  async function checkAuth() {
    if (authChecked) return currentUser;
    
    // if firebase is present we prefer to use it
    if (typeof firebase !== 'undefined' && firebase.auth) {
      try {
        return new Promise((resolve, reject) => {
          const unsubscribe = firebase.auth().onAuthStateChanged(user => {
            authChecked = true;
            unsubscribe();
            if (user) {
              currentUser.email = user.email;
              currentUser.displayName = user.displayName || user.email.split('@')[0];
              currentUser.uid = user.uid;
              console.log('User authenticated via Firebase:', currentUser.email);
              resolve(currentUser);
            } else {
              // not signed in via firebase, maybe server session
              checkServerSession().then(u=>{
                 if(u){resolve(u);} else { redirectToLogin(); reject(new Error('Not authenticated')); }
              });
            }
          }, error => {
            console.error('Auth check error:', error);
            // try server as fallback
            checkServerSession().then(u=>{
                if(u){resolve(u);} else { redirectToLogin(); reject(error); }
            });
          });
        });
      } catch (error) {
        console.error('Authentication check failed:', error);
        return await checkServerSession();
      }
    }
    
    // no firebase available, check server session
    return await checkServerSession();
  }

  async function checkServerSession(){
    try {
      const resp = await fetch('/api/me.php');
      const j = await resp.json();
      if(j.authenticated){
        authChecked = true;
        currentUser.email = j.user.identifier;
        currentUser.displayName = j.user.full_name || j.user.identifier.split('@')[0];
        currentUser.uid = j.user.user_uid;
        console.log('User authenticated via server session:', currentUser.email);
        return currentUser;
      }
    } catch(e){ console.warn('Server session check failed',e); }
    redirectToLogin();
    return null;
  }
  
  function redirectToLogin() {
    // Don't redirect if already on login page
    if (!window.location.pathname.includes('login.php')) {
      window.location.href = 'login.php';
    }
  }
  
  // Logout function
  async function logout() {
    try {
      // server session clear
      try { await fetch('/api/logout.php'); } catch(e){console.warn('Server logout failed',e);}      
      if (firebase.auth) {
        await firebase.auth().signOut();
        console.log('User logged out');
      }
      window.location.href = 'login.php';
    } catch (error) {
      console.error('Logout error:', error);
      alert('Failed to logout. Please try again.');
    }
  }
  
  // Add logout button to pages
  function addLogoutButton() {
    // Check if logout button already exists
    if (document.getElementById('auth-logout-btn')) return;
    
    // Find header controls or create one
    let headerControls = document.querySelector('.header-controls');
    
    if (headerControls) {
      const logoutBtn = document.createElement('button');
      logoutBtn.id = 'auth-logout-btn';
      logoutBtn.className = 'small secondary';
      logoutBtn.textContent = '🚪 Logout';
      logoutBtn.title = 'Sign out';
      logoutBtn.onclick = logout;
      headerControls.appendChild(logoutBtn);
    }
    
    // Also add to toolbar if exists
    const toolbar = document.querySelector('.toolbar');
    if (toolbar && !toolbar.querySelector('#auth-logout-btn-toolbar')) {
      const logoutBtnToolbar = document.createElement('button');
      logoutBtnToolbar.id = 'auth-logout-btn-toolbar';
      logoutBtnToolbar.className = 'small secondary';
      logoutBtnToolbar.textContent = '🚪 Logout';
      logoutBtnToolbar.onclick = logout;
      toolbar.appendChild(logoutBtnToolbar);
    }
    
    // Display user info if there's a suitable location
    const userInfoEl = document.getElementById('user-info');
    if (userInfoEl && currentUser.displayName) {
      userInfoEl.textContent = `Logged in as: ${currentUser.displayName}`;
    }
  }
  
  // Expose functions globally
  window.AuthGuard = {
    checkAuth,
    logout,
    getCurrentUser: () => currentUser,
    isAuthenticated: () => authChecked && currentUser.uid !== null
  };
  
  // Auto-check authentication on page load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', async () => {
      await checkAuth();
      addLogoutButton();
    });
  } else {
    checkAuth().then(() => addLogoutButton()).catch(() => {});
  }
})();
