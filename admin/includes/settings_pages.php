<?php 
    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
?>
<?php $pages = get_all_pages(); ?>
<?php $pages_navbar = get_ordered_navbar_pages(); ?>
<?php include(ROOT_PATH . '/includes/errors.php') ?>
<h2><?php echo $lang['navbar']; ?></h2>
<form method="post" id="form-pages" action="">
    <input type="hidden" name="apply_page_settings" id="holder_submit_menu">
    <table id="table_menu" class="table-form">
        <?php foreach ($pages_navbar as $key => $page_navbar) : ?>
        <tr>
            <th>
                <label for="page_selector"><?php echo $lang['page']; ?> <?php echo $key + 1; ?></label>
            </th>
            <td>
                <select name="page_selector">
                    <option value="<?php echo $pages_navbar[$key]['slug'] ?>" selected><?php echo $pages_navbar[$key]['title'] ?></option>
                <?php foreach ($pages as $page) : ?><?php if ($pages_navbar[$key]['slug'] !== $page['slug']) : ?>
                    <option value="<?php echo $page['slug']; ?>"><?php echo $page['title']; ?></option>
                <?php endif ?>
                <?php endforeach ?>          
                </select>             
                <?php if (count($pages_navbar) - 1 == $key) : ?>
                    <a href="#" id="btn_menu_delete_page"><?php echo $lang['delete']; ?></a>
                <?php endif ?>
                </td>
            </tr>
    <?php endforeach ?>
        <tr>
            <th>
                <label for="page_selector"><?php echo $lang['page']; ?> <?php echo count($pages_navbar) + 1; ?></label>
            </th>
            <td>
                <select name="page_selector" id="selector_menu_last">
                    <option value="" selected></option>
                    <?php foreach ($pages as $page) : ?>
                    <option value="<?php echo $page['slug']; ?>"><?php echo $page['title']; ?></option>
                    <?php endforeach ?>   
                </select>       
            </td>
        </tr>
    </table>
    <button type="submit" class="btn" id="btn_save"><?php echo $lang['save_changes']; ?></button>
</form>
<script>

    const table_menu = document.getElementById('table_menu');

    let selector_menu_last =  document.getElementById('selector_menu_last');
    let btn_menu_delete_page = document.getElementById('btn_menu_delete_page');

    selector_menu_last.addEventListener('change', on_selector_menu_last_change);

    if (btn_menu_delete_page) {

        btn_menu_delete_page.addEventListener('click', remove_page);
        
    }

    document.addEventListener("DOMContentLoaded", function() {

        document.getElementById("selector_menu_last").value = "";

    });

    function on_selector_menu_last_change() {

        if (selector_menu_last.value !== '') {

            add_page();

        }

    }

    function add_page() {
       
        if (btn_menu_delete_page && btn_menu_delete_page.parentNode !== null) {

            btn_menu_delete_page.removeEventListener('click', remove_page);
            btn_menu_delete_page.parentNode.removeChild(btn_menu_delete_page);
            
        }

        selector_menu_last.removeEventListener('change', on_selector_menu_last_change);
        selector_menu_last.removeAttribute('id');
        selector_menu_last.insertAdjacentHTML('afterend', '<a href="#" id="btn_menu_delete_page">Borrar</a>');
        
        btn_menu_delete_page = document.getElementById('btn_menu_delete_page');
        btn_menu_delete_page.addEventListener('click', remove_page);

        const number_of_rows = table_menu.querySelectorAll("tr").length;

        const row_html = 
        `
        <th>
            <label for="page_selector"><?php echo $lang['page']; ?> ` + (number_of_rows + 1) + `</label>
        </th>
        <td>
            <select name="page_selector" id="selector_menu_last">
                <option value="" selected></option>
                <?php foreach ($pages as $page) : ?>
                <option value="<?php echo $page['slug']; ?>"><?php echo $page['title']; ?></option>
                <?php endforeach ?>   
            </select>   
        </td>
        `
        ;

        const new_row = table_menu.insertRow(number_of_rows);
        new_row.innerHTML = row_html;

        selector_menu_last = document.getElementById('selector_menu_last');
        selector_menu_last.addEventListener('change', on_selector_menu_last_change);

    }

    function remove_page() {

        btn_menu_delete_page.removeEventListener('click', remove_page);
        btn_menu_delete_page.parentNode.removeChild(btn_menu_delete_page);

        selector_menu_last.removeEventListener('change', on_selector_menu_last_change);

        table_menu.rows[table_menu.rows.length - 1].remove();

        if (table_menu.rows.length > 0) {

            table_menu.rows[table_menu.rows.length - 1].remove();

        }

        const selector = document.querySelector('tr:last-child select');

        if (selector) {

            selector.insertAdjacentHTML('afterend', '<a href="#" id="btn_menu_delete_page"><?php echo $lang['delete']; ?></a>');

            btn_menu_delete_page = document.getElementById('btn_menu_delete_page');
            btn_menu_delete_page.addEventListener('click', remove_page);
            
        }

        const number_of_rows = table_menu.querySelectorAll("tr").length;

        const row_html = 
        `
        <th>
            <label for="page_selector"><?php echo $lang['page']; ?> ` + (number_of_rows + 1) + `</label>
        </th>
        <td>
            <select name="page_selector" id="selector_menu_last">
                <option value="" selected></option>
            <?php foreach ($pages as $page) : ?>
                <option value="<?php echo $page['slug']; ?>"><?php echo $page['title']; ?></option>
            <?php endforeach ?>   
            </select>   
        </td>
        `
        ;

        const new_row = table_menu.insertRow(number_of_rows);
        new_row.innerHTML = row_html;

        selector_menu_last = document.getElementById('selector_menu_last');
        selector_menu_last.addEventListener('change', on_selector_menu_last_change);

    }

</script>
<script>

    let pages_menu_order = {};

    <?php foreach ($pages_navbar as $key => $page_navbar) : ?>
        <?php if ($page_navbar['url'] === '') : ?>
            pages_menu_order[<?php echo $key; ?>] = { type : 'page', slug : '<?php echo $page_navbar['slug']; ?>'};
        <?php else : ?>
            pages_menu_order[<?php echo $key; ?>] = { type : 'url', slug : '<?php echo $page_navbar['slug']; ?>'};
        <?php endif ?>
    <?php endforeach ?>

    const menu_holder = document.getElementById('holder_submit_menu');
    const save_button = document.getElementById('btn_save');

    const form = document.getElementById('form-pages');

    save_button.addEventListener('click', function() {

        event.preventDefault();

        pages_menu_order = {};

        let i = 0;
        const selects = table_menu.querySelectorAll('td select');
        const valuesArray = Array.from(selects).map(function(select) {

            if (select.value !== '') {

                pages_menu_order[i] = { type : 'page', slug : select.value };
                i++;

            }

        });

        menu_holder.value = JSON.stringify(pages_menu_order);
        console.log(menu_holder.value);
        form.submit();

    });

</script>