<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'schedify_employees';

// Add Employee admin page under the custom menu
function schedify_employee_admin_page() {
    // Check if the submenu already exists to prevent duplication
    global $submenu;
    if (!isset($submenu['schedify-dashboard'])) {
        add_submenu_page(
            'schedify-dashboard',
            'Add/Edit Employee',
            // 'Employees',
            'manage_options',
            'schedify-employees',
            'schedify_render_employee_form'
        );
    }
}
add_action('admin_menu', 'schedify_employee_admin_page');

// Function to render the employee form
function schedify_render_employee_form() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'schedify_employees';

    // Handle add/edit form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $wordpress_user_id = isset($_POST['wordpress_user']) ? intval($_POST['wordpress_user']) : 0;
        $timezone = sanitize_text_field($_POST['timezone']);
        $employee_badge = sanitize_text_field($_POST['employee_badge']);
        
        if (isset($_POST['employee_id']) && !empty($_POST['employee_id'])) {
            // Update employee
            $wpdb->update(
                $table_name,
                [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'wordpress_user_id' => $wordpress_user_id,
                    'timezone' => $timezone,
                    'employee_badge' => $employee_badge
                ],
                ['id' => intval($_POST['employee_id'])],
                ['%s', '%s', '%s', '%s', '%d', '%s', '%s'],
                ['%d']
            );
        } else {
            // Insert new employee
            $wpdb->insert(
                $table_name,
                [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'wordpress_user_id' => $wordpress_user_id,
                    'timezone' => $timezone,
                    'employee_badge' => $employee_badge
                ],
                ['%s', '%s', '%s', '%s', '%d', '%s', '%s']
            );
        }
    }

    // Fetch all employees from the database
    $employees = $wpdb->get_results("SELECT * FROM $table_name");
    $employee_count = count($employees);
    $has_employees = $employee_count > 0;
    ?>

    <div class="wrap">
        <div class="employee-header">
            <h1>Employees <span class="employee-count">(<?php echo $employee_count; ?> Total)</span></h1>
            <button id="add-employee-button" class="button button-primary">+ Add Employee</button>
        </div>

        <?php if ($has_employees): ?>
            <!-- Display the list of employees -->
            <div class="employee-list">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo esc_html($employee->first_name); ?></td>
                                <td><?php echo esc_html($employee->last_name); ?></td>
                                <td><?php echo esc_html($employee->email); ?></td>
                                <td><?php echo esc_html($employee->phone); ?></td>
                                <td>
                                    <a href="?page=schedify-employees&action=edit&id=<?php echo $employee->id; ?>">Edit</a> | 
                                    <a href="?page=schedify-employees&action=delete&id=<?php echo $employee->id; ?>" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-employees">
                <img src="https://example.com/path-to-placeholder-image.png" alt="No Employees" class="no-employees-image">
                <p>You don't have any employees here yet...</p>
                <p>Start by clicking the <strong>Add Employee</strong> button</p>
            </div>
        <?php endif; ?>

        <!-- Modal for Employee Form -->
        <div id="employee-modal" class="modal">
            <div class="modal-content">
                <span class="close-button" id="close-employee-modal">&times;</span>
                <h2 class="modal-title">New Employee</h2>
                
                <!-- Employee Image Placeholder -->
                <div class="employee-image-container">
                    <img src="https://example.com/path-to-placeholder-avatar.png" alt="Employee Avatar" class="employee-avatar">
                </div>

                <form method="post">
                    <input type="hidden" name="employee_id" value="<?php echo isset($_GET['id']) ? esc_attr($_GET['id']) : ''; ?>" />
                    <!-- Tab navigation -->
                    <nav>
                        <ul class="schedify-tabs">
                            <li class="tab-link active" data-tab="details">Details</li>
                            <li class="tab-link" data-tab="assigned-services">Assigned Services</li>
                            <li class="tab-link" data-tab="work-hours">Work Hours</li>
                            <li class="tab-link" data-tab="days-off">Days Off</li>
                            <li class="tab-link" data-tab="special-days">Special Days</li>
                        </ul>
                    </nav>

                    <!-- Tab contents - Details section -->
                    <div id="details" class="tab-content active">
                        <div class="field-row">
                            <div class="field-column">
                                <label for="first_name">First Name:</label>
                                <input type="text" name="first_name" class="form-field" required value="<?php echo isset($_GET['id']) && isset($employee) ? esc_attr($employee->first_name) : ''; ?>" />
                            </div>
                            <div class="field-column">
                                <label for="last_name">Last Name:</label>
                                <input type="text" name="last_name" class="form-field" required value="<?php echo isset($_GET['id']) && isset($employee) ? esc_attr($employee->last_name) : ''; ?>" />
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-column">
                                <label for="email">Email:</label>
                                <input type="email" name="email" class="form-field" required value="<?php echo isset($_GET['id']) && isset($employee) ? esc_attr($employee->email) : ''; ?>" />
                            </div>
                            <div class="field-column">
                                <label for="phone">Phone:</label>
                                <input type="tel" name="phone" class="form-field" value="<?php echo isset($_GET['id']) && isset($employee) ? esc_attr($employee->phone) : ''; ?>" />
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-column">
                                <label for="wordpress_user">WordPress User:</label>
                                <select name="wordpress_user" class="form-field">
                                    <!-- Populate with WordPress users dynamically -->
                                </select>
                            </div>
                            <div class="field-column">
                                <label for="timezone">Timezone:</label>
                                <input type="text" name="timezone" class="form-field" value="UTC" />
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-column">
                                <label for="employee_password">Employee Panel Password:</label>
                                <input type="password" name="employee_password" class="form-field" />
                            </div>
                            <div class="field-column">
                                <label for="employee_badge">Employee Badge:</label>
                                <select name="employee_badge" class="form-field">
                                    <option value="gold">Gold</option>
                                    <option value="silver">Silver</option>
                                    <option value="bronze">Bronze</option>
                                    <!-- Additional options can be added here -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="button button-primary">Save</button>
                        <button type="button" class="button button-secondary" onclick="document.getElementById('employee-modal').classList.remove('show');">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Modal overlay */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        /* Modal content with transition */
        .modal-content {
            background-color: #fff;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.95);
            width: 600px;
            max-width: 90%;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.5);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        
        /* Show modal with transition */
        .modal.show {
            display: block;
            opacity: 1;
        }
        .modal.show .modal-content {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        /* Close button */
        .close-button {
            color: #333;
            float: right;
            font-size: 24px;
            cursor: pointer;
        }

        /* Employee image placeholder styling */
        .employee-image-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .employee-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 2px solid #ddd;
        }

        /* Horizontal tab styling */
        .schedify-tabs {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            border-bottom: 1px solid #ccc;
        }
        .schedify-tabs .tab-link {
            padding: 10px 20px;
            cursor: pointer;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            margin-right: 5px;
            background-color: #f1f1f1;
        }
        .schedify-tabs .tab-link.active {
            background-color: #0073aa;
            color: #fff;
            font-weight: bold;
        }
        
        /* Form fields styling */
        .field-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        .field-column {
            flex: 1;
        }
        .form-field {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        /* Form footer styling */
        .form-footer {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Header and employee count styling */
        .employee-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .employee-count {
            font-size: 0.9em;
            color: #666;
        }
        .no-employees {
            text-align: center;
            color: #555;
        }
        .no-employees-image {
            width: 100px;
            margin-bottom: 20px;
            opacity: 0.6;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addEmployeeButton = document.getElementById('add-employee-button');
            const employeeModal = document.getElementById('employee-modal');
            const closeEmployeeModal = document.getElementById('close-employee-modal');

            // Show modal with animation
            addEmployeeButton.addEventListener('click', function() {
                employeeModal.classList.add('show');
            });

            // Close modal
            closeEmployeeModal.addEventListener('click', function() {
                employeeModal.classList.remove('show');
            });

            // Close modal when clicking outside of content
            window.addEventListener('click', function(event) {
                if (event.target === employeeModal) {
                    employeeModal.classList.remove('show');
                }
            });

            // Tab functionality
            const tabs = document.querySelectorAll('.tab-link');
            const contents = document.querySelectorAll('.tab-content');
            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));
                    tab.classList.add('active');
                    document.getElementById(tab.getAttribute('data-tab')).classList.add('active');
                });
            });
        });
    </script>
    <?php
}
?>
