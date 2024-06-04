<a href="HOME.php" class="btn btn-outline-primary btn-back">Voltar para HOME</a>
<?php
session_start();
include "conexao.php"; // Incluir arquivo de conexão se necessário

// Verificar se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adicionar treino
    if (isset($_POST['username']) && isset($_POST['nome_treino']) && isset($_POST['descricao_treino'])) {
        $username = $_POST['username'];
        $nome_treino = $_POST['nome_treino'];
        $descricao_treino = $_POST['descricao_treino'];

        // Preparar e executar a consulta para inserir o treino
        $stmt = $conn->prepare("INSERT INTO treinos (username, nome, descricao) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $nome_treino, $descricao_treino);

        if ($stmt->execute()) {
            echo "Treino adicionado com sucesso!";
        } else {
            echo "Erro ao adicionar o treino: " . $stmt->error;
        }

        $stmt->close();
    }
    // Adicionar exercício
    if (isset($_POST['exercicio_nome']) && isset($_POST['exercicio_descricao']) && isset($_POST['treino_id'])) {
        $exercicio_nome = $_POST['exercicio_nome'];
        $exercicio_descricao = $_POST['exercicio_descricao'];
        $treino_id = $_POST['treino_id'];

        // Preparar e executar a consulta para inserir o exercício
        $stmt = $conn->prepare("INSERT INTO exercicios (treino_id, nome, descricao) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $treino_id, $exercicio_nome, $exercicio_descricao);

        if ($stmt->execute()) {
            echo "Exercício salvo com sucesso!";
        } else {
            echo "Erro ao salvar o exercício: " . $stmt->error;
        }

        $stmt->close();
    }
    // Remover treino
    if (isset($_POST['delete_treino'])) {
        $treino_id = $_POST['delete_treino'];

        // Preparar e executar a consulta para remover o treino
        $stmt = $conn->prepare("DELETE FROM treinos WHERE id = ?");
        $stmt->bind_param("i", $treino_id);

        if ($stmt->execute()) {
            echo "Treino removido com sucesso!";
        } else {
            echo "Erro ao remover o treino: " . $stmt->error;
        }

        $stmt->close();
    }
    // Pesquisar usuário
    if (isset($_POST['search_user'])) {
        $search_term = $_POST['search_user'];

        // Consulta para buscar usuários com base no termo de pesquisa
        $sql = "SELECT id, username FROM usuarios WHERE username LIKE ?";
        $stmt = $conn->prepare($sql);
        $search_param = "%{$search_term}%";
        $stmt->bind_param('s', $search_param);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Exibir resultados da pesquisa
            echo "<h2>Usuários encontrados:</h2>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li><a href='admin.php?user_id=" . $row['id'] . "'>" . $row['username'] . "</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Nenhum usuário encontrado com o termo de pesquisa '{$search_term}'</p>";
        }

        $stmt->close();
    }
}

// Se um usuário específico foi selecionado, mostrar formulário para adicionar treino e exercícios
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $user_name = '';

    // Consulta para obter o nome do usuário
    $sql = "SELECT username FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_name = $row['username'];
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração de Treinos</title>
    <link rel="stylesheet" href="styles.css"> <!-- Estilos CSS -->
</head>
<body>
    <h1>Administração de Treinos</h1>

    <h2>Adicionar Treino para <?php echo $user_name; ?></h2>
    <form method="post" action="">
        <input type="hidden" name="username" value="<?php echo $user_name; ?>">
        <label for="nome_treino">Nome do Treino:</label>
        <input type="text" id="nome_treino" name="nome_treino" required>
        <label for="descricao_treino">Descrição do Treino:</label>
        <input type="text" id="descricao_treino" name="descricao_treino" required>
        <button type="submit">Adicionar Treino</button>
    </form>

    <h2>Adicionar Exercício</h2>
    <form method="post" action="">
        <label for="treino_id">Treino:</label>
        <select id="treino_id" name="treino_id">
        <?php
        // Consulta para obter treinos do usuário selecionado
        $sql = "SELECT id, nome FROM treinos WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $user_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['nome']}</option>";
            }
        } else {
            echo "<option disabled selected>Nenhum treino encontrado</option>";
        }
        ?>
        </select>
        <label for="exercicio_nome">Nome do Exercício:</label>
        <input type="text" id="exercicio_nome" name="exercicio_nome" required>
        <label for="exercicio_descricao">Descrição do Exercício:</label>
        <input type="text" id="exercicio_descricao" name="exercicio_descricao" required>
        <button type="submit">Adicionar Exercício</button>
    </form>

    <h2>Remover Treino</h2>
    <form method="post" action="">
        <label for="delete_treino">Selecione o treino a ser removido:</label>
        <select id="delete_treino" name="delete_treino">
        <?php
        // Consulta para obter treinos do usuário selecionado
        $sql = "SELECT id, nome FROM treinos WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $user_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['nome']}</option>";
            }
        } else {
            echo "<option disabled selected>Nenhum treino encontrado</option>";
        }
        ?>
        </select>
        <button type="submit">Remover Treino</button>
    </form>

    <h2>Pesquisar Usuário</h2>
    <form method="post" action="">
        <label for="search_user">Digite o nome do usuário:</label>
        <input type="text" id="search_user" name="search_user" required>
        <button type="submit">Pesquisar</button>
    </form>
</body>
</html>
<?php
} else {
    // Se nenhum usuário foi selecionado, mostrar apenas o formulário de pesquisa
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração de Treinos</title>
    <link rel="stylesheet" href="styles.css"> <!-- Estilos CSS -->
</head>
<body>
    <h1>Administração de Treinos</h1>

    <h2>Pesquisar Usuário</h2>
    <form method="post" action="">
        <label for="search_user">Digite o nome do usuário:</label>
        <input type="text" id="search_user" name="search_user" required>
        <button type="submit">Pesquisar</button>
    </form>
</body>
</html>
<?php
}
$conn->close();
?>

