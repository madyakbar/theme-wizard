jQuery(document).ready(function($) {

    // Function to check if a theme is selected
    function checkThemeSelected() {
        var selected = $('#theme-selection-form input[type="radio"]:checked').length > 0;
        $('#next-step-btn').prop('disabled', !selected);
    }

    // Check if a theme is selected initially
    checkThemeSelected();

    // Enable "Next" button when a theme is selected
    $('#theme-selection-form input[type="radio"]').change(function() {
        checkThemeSelected();
    });

    // Next button click event for Step 1
    $('#next-step-btn').click(function() {
        var selectedTheme = $('#theme-selection-form input[type="radio"]:checked').val();
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'theme_selection',
                selected_theme: selectedTheme // Pass selected theme data
            },
            success: function(response) {
                $('#message-container').html(response);
                $('#theme-selection-form').hide();
                $('#next-step-btn').hide();
                $('.head__').hide();
                $('.bg__').hide();
                
                initializeSecondStep(); // Initialize the second step
            }
        });
    });
function initializeSecondStep(){
 


    // Next button click event for Step 2
   $('#next-step-btn-2').click(function() {
    var title = $('#title').val();
    var logo = $('#logo')[0].files[0]; // Get the logo file
    var headerMenu = $('#header-menu').is(':checked') ? '1' : '0';
    var footerMenu = $('#footer-menu').is(':checked') ? '1' : '0';
    
    var formData = new FormData();
    formData.append('action', 'customize_theme');
    formData.append('title', title);
    formData.append('logo', logo);
    formData.append('header_menu', headerMenu);
    formData.append('footer_menu', footerMenu);
    
    
    if (!$("#header-menu").is(":checked")) {
        formData.append('header_menu', '0');
    }
    if (!$("#footer-menu").is(":checked")) {
        formData.append('footer_menu', '0');
    }

    $.ajax({
        url: ajax_object.ajax_url,
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false, // Ensure that data is not processed by jQuery
        success: function(response) {
            $('#message-container').html(response);
            $('#customize-theme-form').hide();
            $('#next-step-btn-2').hide();
            initializeThirdStep();
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
        }
    });
});
$('#create-menu').change(function() {
    if ($(this).is(':checked')) {
        $('.header-menu, .footer-menu').show();
    } else {
        $('.header-menu, .footer-menu').hide();
    }
});



   
}

function initializeThirdStep(){
   

    // Next button click event for Step 3
    $('#next-step-btn-3').click(function() {
        var selectedDemoContent = $('input[name="demo_content"]:checked').val();
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'choose_demo_content',
                selected_demo_content: selectedDemoContent
            },
            success: function(response) {
                $('#message-container').html(response);
                $('#choose-demo-content-form').hide();
                $('#next-step-btn-3').hide();
                initializeFourthStep(); // Initialize the fourth step
            }
        });
    });
}

function initializeFourthStep(){
 
    // Next button click event for Step 4
    $('#next-step-btn-4').click(function() {
        var selectedPlugins = $('input[name="selected_plugin[]"]:checked').map(function() {
            return this.value;
        }).get();
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'choose_required_plugin',
                selected_plugin: selectedPlugins
            },
            success: function(response) {
                $('#message-container').html(response);
                $('#select-plugins-form').hide();
                $('#next-step-btn-4').hide();
                initializeFifthStep();
            }
        });
    });
}
function initializeFifthStep() {
    $('#finish-btn').click(function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Show loading spinner
        $('#loading-spinner').show();

        // Open the modal box
       // $('#popup-overlay').show();

        var selectedTheme = $('#theme-selection-form input[type="radio"]:checked').val();
        var siteTitle = $('#title').val();
        var siteLogo = $('#logo').val();
        var headerMenu = $('#custom_header_menu').val();
        var footerMenu = $('#custom_footer_menu').val();
        var demoContent = $('#selected_demo_content').val();

        if (headerMenu === '0') {
            $('.wp-block-navigation__container, .wp-block-page-list').hide();
            $('body').addClass('no-header-menu');
        } else {
            $('.wp-block-navigation__container, .wp-block-page-list').show();
        }

        var selectedPluginsArray = $('#selected_plugins').data('plugins');
        var selectedPluginsString = JSON.stringify(selectedPluginsArray);
        var selectedPlugins = JSON.parse(selectedPluginsString);

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'install_selected_theme',
                selected_theme: selectedTheme,
                site_title: siteTitle,
                site_logo: siteLogo,
                header_menu: headerMenu,
                footer_menu: footerMenu,
                demo_content: demoContent,
                selected_plugins: selectedPlugins
            },
            success: function(response) {
                // Hide loading spinner
                $('#loading-spinner').hide();
                 $('#popup-overlay').show();


                // Clear previous content
                $('#popup-content').empty();
                
                // Check if response is a string
                if (typeof response === 'string') {
                    // Remove brackets and quotes from the string
                    response = response.replace(/[\[\]"]+/g, '');
                    // Split the string into an array using the ',' delimiter
                    response = response.split(',');
                }
                
                // Iterate over each message in the array and append it to the popup content
                response.forEach(function(message) {
                    $('#popup-content').append('<p>' + message.trim() + '</p>');
                });
                
                // Show the modal dialog
                $('#popup-modal').show();
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                // Hide loading spinner
                $('#loading-spinner').hide();
            }
        });
    });

    $('#close-modal').click(function() {
        // Close the modal box when the user clicks the close button
        $('#popup-overlay').hide();
    });
}













});
