// script.js

document.addEventListener('DOMContentLoaded', function() {

    // Formulário de movimento

    const formMovimento = document.getElementById('formMovimento');

    if (formMovimento) {

        formMovimento.addEventListener('submit', async (e) => {

            e.preventDefault();

            const formData = new FormData(formMovimento);

            

            try {

                const response = await fetch('movimento.php', {

                    method: 'POST',

                    body: formData

                });

                

                const result = await response.json();

                if (result.success) {

                    alert('Movimento salvo com sucesso!');

                    formMovimento.reset();

                } else {

                    alert('Erro ao salvar movimento');

                }

            } catch (error) {

                console.error('Erro:', error);

                alert('Erro ao processar requisição');

            }

        });

    }



    // Formulário de relatório

    const formRelatorio = document.getElementById('formRelatorio');

    if (formRelatorio) {

        formRelatorio.addEventListener('submit', async (e) => {

            e.preventDefault();

            const formData = new FormData(formRelatorio);

            

            try {

                const response = await fetch('get_relatorio.php', {

                    method: 'POST',

                    body: formData

                });

                

                const movimentos = await response.json();

                atualizarTabelaRelatorio(movimentos);

            } catch (error) {

                console.error('Erro:', error);

                alert('Erro ao buscar relatório');

            }

        });

    }

});



function atualizarTabelaRelatorio(movimentos) {

    const tbody = document.querySelector('#tabelaRelatorio tbody');

    tbody.innerHTML = '';

    

    movimentos.forEach(mov => {

        const tr = document.createElement('tr');

        tr.innerHTML = `

            <td>${new Date(mov.data_salvo).toLocaleDateString()}</td>

            <td>${mov.lote}</td>

            <td>${mov.nome_tipo}</td>

            <td>R$ ${parseFloat(mov.valor).toFixed(2)}</td>

            <td>${mov.nome_usuario}</td>

        `;

        tbody.appendChild(tr);

    });

}