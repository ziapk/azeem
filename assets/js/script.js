$.validator.addMethod("customemail", 
  function(value, element) {
     return /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(value);
  }, 
  "Please enter a valid email address."
);

function toggleSettingMenu() {
  $('#settingMenu').toggleClass('active');
} 