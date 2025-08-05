// Tailwind CSS Configuration Script
// This script configures Tailwind CSS with custom breakpoints and extends the default configuration

// Custom Tailwind Configuration
const tailwindConfig = {
  theme: {
    screens: {
      'xs': '475px',     // Extra small devices
      'sm': '640px',     // Small devices
      'md': '768px',     // Medium devices (tablets)
      'lg': '1024px',    // Large devices (laptops)
      'xl': '1280px',    // Extra large devices
      '2xl': '1536px',   // 2X large devices
    },
    extend: {
      colors: {
        'brand': {
          'orange': '#ea580c',
          'orange-dark': '#c2410c',
          'black': '#000000',
        },
        'dark': {
          'main': '#1e1e1e',
          'card': '#242424',
          'sidebar': '#242526',
          'border': '#3a3b3c',
        },
        'text': {
          'light': '#e4e6eb',
        }
      },
      fontFamily: {
        'sans': ['Poppins', 'ui-sans-serif', 'system-ui'],
      },
      spacing: {
        '18': '4.5rem',
        '72': '18rem',
        '84': '21rem',
        '96': '24rem',
      },
      maxWidth: {
        '8xl': '88rem',
        '9xl': '96rem',
      },
      backdropBlur: {
        'xs': '2px',
      },
      animation: {
        'fade-in-up': 'fadeInUp 0.6s ease-out forwards',
        'slide-in-right': 'slideInRight 0.6s ease-out forwards',
        'bounce-gentle': 'bounce 2s infinite',
      },
      keyframes: {
        fadeInUp: {
          '0%': {
            opacity: '0',
            transform: 'translateY(30px)',
          },
          '100%': {
            opacity: '1',
            transform: 'translateY(0)',
          },
        },
        slideInRight: {
          '0%': {
            opacity: '0',
            transform: 'translateX(30px)',
          },
          '100%': {
            opacity: '1',
            transform: 'translateX(0)',
          },
        },
      },
      boxShadow: {
        'glow': '0 0 20px rgba(234, 88, 12, 0.3)',
        'glow-lg': '0 0 40px rgba(234, 88, 12, 0.4)',
        'dark': '0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2)',
        'dark-lg': '0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2)',
      },
      borderRadius: {
        '4xl': '2rem',
        '5xl': '2.5rem',
      }
    },
  },
  plugins: [],
};

// Function to configure Tailwind CSS
function configureTailwind() {
  if (typeof tailwind !== 'undefined') {
    tailwind.config = tailwindConfig;
  }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  configureTailwind();
  
  // Add responsive utility classes for custom breakpoints
  addCustomBreakpointClasses();
  
  // Initialize responsive navigation
  initResponsiveNavigation();
  
  // Initialize responsive sidebar
  initResponsiveSidebar();
});

// Add custom breakpoint classes
function addCustomBreakpointClasses() {
  const style = document.createElement('style');
  style.textContent = `
    /* Custom breakpoint utilities */
    @media (min-width: 475px) {
      .xs\\:block { display: block !important; }
      .xs\\:hidden { display: none !important; }
      .xs\\:flex { display: flex !important; }
      .xs\\:grid { display: grid !important; }
      .xs\\:inline { display: inline !important; }
      .xs\\:inline-block { display: inline-block !important; }
      .xs\\:inline-flex { display: inline-flex !important; }
      .xs\\:w-auto { width: auto !important; }
      .xs\\:w-full { width: 100% !important; }
      .xs\\:h-auto { height: auto !important; }
      .xs\\:h-full { height: 100% !important; }
      .xs\\:text-left { text-align: left !important; }
      .xs\\:text-center { text-align: center !important; }
      .xs\\:text-right { text-align: right !important; }
      .xs\\:justify-start { justify-content: flex-start !important; }
      .xs\\:justify-center { justify-content: center !important; }
      .xs\\:justify-end { justify-content: flex-end !important; }
      .xs\\:items-start { align-items: flex-start !important; }
      .xs\\:items-center { align-items: center !important; }
      .xs\\:items-end { align-items: flex-end !important; }
    }
    
    /* Responsive navigation utilities */
    .nav-responsive {
      @media (max-width: 767px) {
        transform: translateX(100%);
        transition: transform 0.3s ease;
      }
    }
    
    .nav-responsive.active {
      @media (max-width: 767px) {
        transform: translateX(0);
      }
    }
    
    /* Responsive sidebar utilities */
    .sidebar-responsive {
      @media (max-width: 767px) {
        width: 0;
        min-width: 0;
        overflow: hidden;
        padding: 0;
      }
    }
    
    /* Messages responsive layout */
    .messages-responsive {
      @media (max-width: 767px) {
        flex-direction: column;
        height: calc(100vh - 80px);
      }
    }
    
    .messages-sidebar-responsive {
      @media (max-width: 767px) {
        width: 100%;
        height: 40%;
        border-right: none;
        border-bottom: 1px solid var(--border-dark);
      }
    }
    
    .messages-content-responsive {
      @media (max-width: 767px) {
        height: 60%;
      }
    }
  `;
  document.head.appendChild(style);
}

// Initialize responsive navigation
function initResponsiveNavigation() {
  const navToggle = document.getElementById('nav-toggle');
  const navMenu = document.getElementById('nav-menu');
  
  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function() {
      navMenu.classList.toggle('active');
    });
    
    // Close nav when clicking outside
    document.addEventListener('click', function(event) {
      if (!navToggle.contains(event.target) && !navMenu.contains(event.target)) {
        navMenu.classList.remove('active');
      }
    });
  }
}

// Initialize responsive sidebar
function initResponsiveSidebar() {
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebar = document.getElementById('sidebar');
  
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function() {
      sidebar.classList.toggle('sidebar-collapsed');
    });
  }
  
  // Auto-hide sidebar on small screens
  function handleSidebarResponsive() {
    if (window.innerWidth < 768 && sidebar) {
      sidebar.classList.add('sidebar-responsive');
    } else if (sidebar) {
      sidebar.classList.remove('sidebar-responsive');
    }
  }
  
  window.addEventListener('resize', handleSidebarResponsive);
  handleSidebarResponsive(); // Call on initial load
}

// Utility function to handle responsive layout shifts
function preventLayoutShift() {
  const elements = document.querySelectorAll('.no-layout-shift');
  elements.forEach(element => {
    element.style.willChange = 'auto';
    element.style.transform = 'translateZ(0)';
  });
}

// Initialize layout shift prevention
document.addEventListener('DOMContentLoaded', preventLayoutShift);

// Export configuration for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
  module.exports = tailwindConfig;
} 