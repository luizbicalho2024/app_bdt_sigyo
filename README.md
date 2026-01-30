# 🚛 Sigyo BDT - Sistema de Gestão de Frotas e Boletim Diário de Tráfego

O **Sigyo BDT** é uma solução web completa para gestão de frotas, focada no controle de jornadas de motoristas, checklists veiculares digitalizados e telemetria básica via GPS do smartphone.

O projeto foi desenhado para eliminar a necessidade de aplicativos nativos pesados, funcionando como um **Web App Responsivo** altamente otimizado para navegadores móveis (para o motorista) e um **Dashboard Administrativo** robusto (para o gestor).

---

## 🚀 Funcionalidades Principais

### 📱 Módulo Motorista (Mobile Web)

* **Check-in Inteligente:** Validação de quilometragem (impede inserção menor que a anterior) e travamento do botão até obtenção do sinal GPS.
* **Checklist Digital (Saída e Retorno):** Interface "Fat Finger Friendly" (botões grandes).
* *Lógica Condicional:* Se um item for marcado como "Avaria/Ruim", exige **foto obrigatória** (câmera direta) e observação.


* **Cronômetro de Viagem:** Contagem de tempo em tempo real.
* **Botão de Pânico:** Envio imediato de alerta com geolocalização para o gestor.
* **Registro de Ocorrências:** Reporte de pneus furados, colisões ou falhas mecânicas com fotos.
* **Dashboard Pessoal:** Resumo de KM rodados e viagens realizadas no dia atual.

### 💻 Módulo Gestor (Dashboard)

* **Visão em Tempo Real:** Cards interativos mostrando veículos em trânsito e alertas.
* **Timeline de Viagem:** Rastreamento cronológico de eventos (Início, Checklist, Pânico, Fim) com links diretos para o Google Maps.
* **Auditoria de Checklists:** Visualização lado a lado do estado do veículo na saída vs. retorno, com galeria de fotos das avarias.
* **Gestão de Usuários:** Cadastro de motoristas e gestores com controle de CNH.
* **Configurações:** Upload de logo da empresa para personalização White-label.

---

## 🛠️ Arquitetura e Tecnologias

O projeto utiliza uma arquitetura **MVC simplificada** (Model-View-Controller) sem o uso de frameworks pesados, garantindo alta performance em hospedagens compartilhadas e facilidade de manutenção.

* **Backend:** PHP (Vanilla) com PDO para segurança contra SQL Injection.
* **Frontend:** HTML5, CSS3 (Custom Design System) e JavaScript Puro (Vanilla JS).
* **Banco de Dados:** MySQL/MariaDB.
* **APIs do Navegador:**
* `Geolocation API`: Para captura de coordenadas (Latitude/Longitude).
* `Media Capture API`: Para forçar o uso da câmera traseira (`capture="environment"`) nos checklists.



---

## 📂 Estrutura do Projeto

```text
/sigyo-bdt
│
├── /admin                # Área do Gestor (Desktop)
│   ├── dashboard.php     # Visão geral e métricas
│   ├── ver_viagem.php    # Detalhes, Timeline e Fotos
│   └── usuarios.php      # Gestão de pessoas
│
├── /mobile               # Área do Motorista (Mobile First)
│   ├── index.php         # Home/Dashboard do motorista
│   ├── checklist.php     # Lógica de vistoria com upload
│   └── viagem.php        # Tela de trânsito e Pânico
│
├── /assets               # Recursos estáticos
│   ├── css/style.css     # Design System responsivo
│   └── img/              # Logos e ícones
│
├── /config
│   └── db.php            # Conexão PDO e Configuração de Timezone
│
├── /includes             # Componentes reutilizáveis
│   ├── auth.php          # Controle de sessão e permissões
│   └── header_*.php      # Cabeçalhos dinâmicos
│
├── /uploads              # Armazenamento de fotos dos checklists
│
└── banco.sql             # Script de criação do banco de dados

```

---

## 🧠 Regras de Negócio e Lógica (Backend)

### 1. Travamento por GPS

O sistema impede o envio de formulários críticos (Check-in, Checklist, Checkout) se o dispositivo não fornecer coordenadas GPS precisas. Isso é feito via JavaScript no front-end (desabilitando o botão submit) e validado no PHP (rejeitando a requisição se `lat/lng` forem nulos).

### 2. Validação de Hodômetro

Ao iniciar uma viagem, o sistema compara o KM inserido com o `hodometro_atual` do veículo no banco de dados.

* **Regra:** `Novo KM >= Último KM`.
* Isso evita fraudes ou erros de digitação que poderiam "rejuvenescer" a quilometragem do carro.

### 3. Fluxo de Auditoria Visual

O upload de imagens não permite acesso à galeria em dispositivos móveis compatíveis, forçando o motorista a tirar a foto da avaria no momento da inspeção, garantindo a veracidade da informação (atributo `capture="environment"`).

### 4. Fuso Horário

Todo o sistema força o Timezone para `America/Manaus` (ou conforme configuração em `config/db.php`) tanto no PHP quanto nas sessões do MySQL, garantindo que os relatórios de horas batam com a realidade local, independente do servidor de hospedagem.

---

## 🗄️ Estrutura do Banco de Dados

O sistema utiliza um banco relacional MySQL. Abaixo o esquema simplificado:

* **`empresas`**: Suporte a multi-tenancy (vários clientes no mesmo banco).
* **`motoristas`**: Usuários do sistema (Gestor/Motorista). Senhas em MD5 (para protótipo) ou Hash.
* **`veiculos`**: Frota cadastrada com controle de status (`DISPONIVEL`, `EM_VIAGEM`).
* **`viagens`**: Tabela central. Relaciona Motorista + Veículo + Horários + KMs.
* **`checklist_respostas`**: Itens inspecionados. Armazena status, observação e URL da foto.
* **`telemetria`**: Tabela de log de eventos. Grava cada ação importante (Checkin, Checkout, Pânico) com Timestamp e Coordenadas GPS.

---

## 🚀 Como Instalar e Rodar

### Pré-requisitos

* Servidor Web (Apache/Nginx).
* PHP 7.4 ou superior.
* MySQL 5.7 ou superior.

### Passo a Passo

1. **Clone o Repositório:**
```bash
git clone https://github.com/SEU-USUARIO/sigyo-bdt.git

```


2. **Configurar Banco de Dados:**
* Crie um banco de dados MySQL (ex: `sigyo_db`).
* Importe o arquivo `banco.sql` localizado na raiz do projeto.


3. **Configurar Conexão:**
* Edite o arquivo `config/db.php`.
* Ajuste as variáveis `$host`, `$dbname`, `$user`, `$pass` conforme seu ambiente.


4. **Permissões de Pasta:**
* Garanta que a pasta `uploads/` e `uploads/checklists/` tenham permissão de escrita (755 ou 777 dependendo do ambiente).


5. **Acesso:**
* **Login Gestor:** `admin@sigyo.com` / `123456`
* **Login Motorista:** `motorista@sigyo.com` / `123456`



---

## 🔮 Melhorias Futuras (Roadmap)

* [ ] Implementar paginação nas tabelas de histórico.
* [ ] Geração de relatórios em PDF/Excel.
* [ ] Gráficos estatísticos (Chart.js) no Dashboard.
* [ ] Integração com API de mapas para calcular rotas percorridas.
* [ ] Criptografia de senhas com `password_hash` (Argon2).

---

## 📄 Licença

Este projeto está sob a licença MIT. Sinta-se livre para usar, estudar e modificar.

**Desenvolvido com foco em UX Mobile e Praticidade Operacional.**
