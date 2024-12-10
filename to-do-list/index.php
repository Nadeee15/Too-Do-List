<?php
require 'db_conn.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Enhanced To-Do List</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filter-buttons {
            margin-bottom: 20px;
        }

        .filter-buttons button {
            margin-right: 10px;
        }

        .success-message {
            color: green;
            font-weight: bold;
        }

        .error-message {
            color: red;
            font-weight: bold;
        }

        .todo-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .edit-input {
            display: none;
        }
    </style>
</head>

<body>
    <div class="main-section">
        <div class="add-section">
            <form id="add-form" action="app/add.php" method="POST" autocomplete="off">
                <input type="text" id="title" name="title" placeholder="What do you need to do?" />
                <button type="submit">Add &nbsp; <span>&#43;</span></button>
            </form>
            <div class="messages">
                <p class="success-message" style="display: none;">Task added successfully!</p>
                <p class="error-message" style="display: none;">Error adding task. Please try again.</p>
            </div>
        </div>

        <?php
        $todos = $conn->query("SELECT * FROM todos ORDER BY id DESC");
        ?>

        <div class="filter-buttons">
            <button id="filter-all">All</button>
            <button id="filter-completed">Completed</button>
            <button id="filter-incomplete">Incomplete</button>
        </div>

        <div class="show-todo-section" id="todo-list">
            <?php if ($todos->rowCount() <= 0) { ?>
                <div class="todo-item">
                    <div class="empty">
                        <img src="img/f.png" width="100%" />
                        <img src="img/Ellipsis.gif" width="80px">
                    </div>
                </div>
            <?php } ?>

            <?php while ($todo = $todos->fetch(PDO::FETCH_ASSOC)) { ?>
                <div class="todo-item" data-status="<?php echo $todo['checked'] ? 'completed' : 'incomplete'; ?>">
                    <span id="<?php echo $todo['id']; ?>" class="remove-to-do">x</span>
                    <?php if ($todo['checked']) { ?>
                        <input type="checkbox" class="check-box" data-todo-id="<?php echo $todo['id']; ?>" checked />
                        <h2 class="checked" contenteditable="false"><?php echo $todo['title'] ?></h2>
                    <?php } else { ?>
                        <input type="checkbox" data-todo-id="<?php echo $todo['id']; ?>" class="check-box" />
                        <h2 contenteditable="false"><?php echo $todo['title'] ?></h2>
                    <?php } ?>
                    <br>
                    <small>created: <?php echo date('Y-m-d H:i:s', strtotime($todo['date_time'])); ?></small>
                    <button class="edit-btn" data-todo-id="<?php echo $todo['id']; ?>">Edit</button>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="js/jquery-3.2.1.min.js"></script>

    <script>
        $(document).ready(function () {
            // Remove task
            $('.remove-to-do').click(function () {
                const id = $(this).attr('id');

                $.post("app/remove.php",
                    {
                        id: id
                    },
                    (data) => {
                        if (data) {
                            $(this).parent().hide(600);
                        }
                    }
                );
            });

            // Toggle task completion
            $(".check-box").click(function (e) {
                const id = $(this).attr('data-todo-id');

                $.post('app/check.php',
                    {
                        id: id
                    },
                    (data) => {
                        if (data != 'error') {
                            const h2 = $(this).next();
                            if (data === '1') {
                                h2.removeClass('checked');
                                $(this).parent().attr('data-status', 'incomplete');
                            } else {
                                h2.addClass('checked');
                                $(this).parent().attr('data-status', 'completed');
                            }
                        }
                    }
                );
            });

            // Filter tasks
            $('#filter-all').click(function () {
                $('.todo-item').show();
            });

            $('#filter-completed').click(function () {
                $('.todo-item').hide();
                $('.todo-item[data-status="completed"]').show();
            });

            $('#filter-incomplete').click(function () {
                $('.todo-item').hide();
                $('.todo-item[data-status="incomplete"]').show();
            });

            // Edit task
            $('.edit-btn').click(function () {
                const parent = $(this).parent();
                const h2 = parent.find('h2');

                if (h2.attr('contenteditable') === 'false') {
                    h2.attr('contenteditable', 'true').focus();
                    $(this).text('Save');
                } else {
                    const id = $(this).attr('data-todo-id');
                    const newTitle = h2.text();

                    $.post('app/edit.php',
                        {
                            id: id,
                            title: newTitle
                        },
                        (data) => {
                            if (data != 'error') {
                                h2.attr('contenteditable', 'false');
                                $(this).text('Edit');
                            }
                        }
                    );
                }
            });

            // Form validation
            $('#add-form').submit(function (e) {
                const title = $('#title').val();

                if (!title.trim()) {
                    e.preventDefault();
                    $('.error-message').show().fadeOut(3000);
                } else {
                    $('.success-message').show().fadeOut(3000);
                }
            });
        });
    </script>
</body>

</html>