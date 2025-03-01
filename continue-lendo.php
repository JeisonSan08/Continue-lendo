<?php
/*
Plugin Name: Ocultar Metade dos Posts por Categoria
Description: Oculta metade do conteúdo dos posts para categorias selecionadas e exibe um botão "Continue lendo".
Version: 1.0
Author: Jeison Ferreira
*/

if (!defined('ABSPATH')) {
    exit; // Segurança
}

// Adiciona uma opção no painel de configurações para selecionar a categoria
function opcao_ocultar_categoria() {
    add_options_page(
        'Ocultar Metade dos Posts',
        'Ocultar Posts',
        'manage_options',
        'ocultar-posts-config',
        'pagina_config_ocultar_posts'
    );
}
add_action('admin_menu', 'opcao_ocultar_categoria');

// Página de configurações no painel de administração
function pagina_config_ocultar_posts() {
    ?>
    <div class="wrap">
        <h1>Configuração: Ocultar Metade dos Posts</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ocultar_posts_config');
            do_settings_sections('ocultar-posts-config');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Registra a opção de categoria nas configurações do WordPress
function registrar_config_ocultar_posts() {
    register_setting('ocultar_posts_config', 'ocultar_categoria');

    add_settings_section(
        'ocultar_posts_section',
        'Selecione a Categoria para Ocultar Posts',
        null,
        'ocultar-posts-config'
    );

    add_settings_field(
        'ocultar_categoria',
        'Categoria:',
        'campo_select_categoria',
        'ocultar-posts-config',
        'ocultar_posts_section'
    );
}
add_action('admin_init', 'registrar_config_ocultar_posts');

// Campo de seleção de categoria
function campo_select_categoria() {
    $categoria_selecionada = get_option('ocultar_categoria');
    $categorias = get_categories(array('hide_empty' => false));

    echo '<select name="ocultar_categoria">';
    echo '<option value="">Selecione uma categoria</option>';
    foreach ($categorias as $categoria) {
        $selected = ($categoria_selecionada == $categoria->slug) ? 'selected' : '';
        echo '<option value="' . esc_attr($categoria->slug) . '" ' . $selected . '>' . esc_html($categoria->name) . '</option>';
    }
    echo '</select>';
}

// Modifica o conteúdo do post se ele pertence à categoria selecionada
function ocultar_metade_do_post($content) {
    if (is_single()) {
        $categoria_selecionada = get_option('ocultar_categoria');
        
        if (!empty($categoria_selecionada) && has_category($categoria_selecionada)) {
            $partes = explode(' ', $content);
            $meio = ceil(count($partes) / 2);
            $primeira_parte = implode(' ', array_slice($partes, 0, $meio));
            $segunda_parte = implode(' ', array_slice($partes, $meio));

            $novo_conteudo = '<div class="oculto-conteudo">
                <div class="primeira-parte">' . $primeira_parte . '</div>
                <div class="segunda-parte" style="display: none;">' . $segunda_parte . '</div>
                <button class="read-more">Continue lendo</button>
            </div>';

            return $novo_conteudo;
        }
    }
    return $content;
}
add_filter('the_content', 'ocultar_metade_do_post');

// Adicionando JavaScript para "Continue lendo"
function ocultar_metade_post_script() {
    if (is_single()) {
        ?>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".read-more").forEach(function(button) {
                button.addEventListener("click", function() {
                    var post = this.closest(".oculto-conteudo");
                    post.querySelector(".segunda-parte").style.display = "block";
                    this.style.display = "none";
                });
            });
        });
        </script>
        <style>
        .read-more {
            background-color: #0073aa;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            margin-top: 10px;
            display: inline-block;
            border-radius: 5px;
        }

        .read-more:hover {
            background-color: #005177;
        }
        </style>
        <?php
    }
}
add_action('wp_footer', 'ocultar_metade_post_script');
