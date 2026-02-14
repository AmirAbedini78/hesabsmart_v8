<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

return [
    'permissions' => 'Permissões',
    'role' => 'Cargo',
    'roles' => 'Cargos',
    'name' => 'Nome',
    'create' => 'Criar Cargo',
    'edit' => 'Editar Cargo',
    'created' => 'Cargo criado com sucesso',
    'updated' => 'Cargo atualizado com sucesso',
    'deleted' => 'Cargo excluído com sucesso',
    'granted' => 'Concedido',
    'revoked' => 'Revogado',
    'capabilities' => [
        'access' => 'Acesso',
        'view' => 'Visualizar',
        'delete' => 'Excluir',
        'bulk_delete' => 'Exclusão em massa',
        'edit' => 'Editar',
        'all' => 'Todos :resourceName',
        'owning_only' => 'Somente proprietário',
    ],
    'view_non_authorized_after_record_create' => 'Sua conta não está autorizada a visualizar este registro, pois você não é o proprietário do registro. Após ser redirecionado desta página, você não poderá acessar o registro.',
    'empty_state' => [
        'title' => 'Sem cargos',
        'description' => 'Comece criando um novo cargo.',
    ],
];
