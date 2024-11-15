function toggleSubmenu(menuId) {
    // Get all submenu elements
    const submenus = document.querySelectorAll('.submenu');
    const menuItems = document.querySelectorAll('.menu-item');

    // Close all submenus and remove active class
    submenus.forEach(submenu => submenu.classList.remove('active'));
    menuItems.forEach(item => item.classList.remove('active'));

    // Toggle the clicked submenu's visibility and set active state
    const targetMenu = document.getElementById(menuId);
    const targetItem = targetMenu.previousElementSibling;

    if (targetMenu && targetItem) {
        targetMenu.classList.add('active');
        targetItem.classList.add('active');
    }
}
