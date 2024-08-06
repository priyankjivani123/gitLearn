<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-assistant
 * @version   1.3.11
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Assistant\Service\ContextMaker;

use Magento\Framework\Registry;

class TicketContext extends AbstractContext
{
    private $registry;

    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function context(): ?array
    {
        /** @var \Mirasvit\Helpdesk\Model\Ticket $ticket */
        $ticket = $this->registry->registry('current_ticket');

        if (!$ticket) {
            return null;
        }

        $data = [
            [
                'id'    => 'ticket.subject',
                'label' => 'Subject',
                'value' => $ticket->getSubject(),
            ],
            [
                'id'    => 'ticket.customer',
                'label' => 'Customer',
                'value' => $ticket->getCustomerName(),
            ],
        ];

        $messages = '';
        foreach ($ticket->getMessages()->setOrder('created_at', 'asc') as $message) {
            if ($message->getType() !== 'public') {
                continue;
            }

            $messages .= $message->getUserName() ? 'Assistant' : 'Customer';
            $messages .= ': ';
            $messages .= '"' . $this->clear((string)$message->getBody()) . '"' . PHP_EOL;
        }

        $data[] = [
            'id'    => 'ticket.messages',
            'label' => 'History',
            'value' => $messages,
        ];

        $firstMessage = $ticket->getMessages()
            ->addFieldToFilter('main_table.user_id', ['null' => true])
            ->setOrder('created_at', 'asc')
            ->getFirstItem();
        $data[]       = [
            'id'    => 'ticket.firstMessage',
            'label' => 'First Message',
            'value' => $this->clear((string)$firstMessage->getBody()),
        ];

        $lastMessage = $ticket->getMessages()
            ->addFieldToFilter('main_table.user_id', ['null' => true])
            ->setOrder('created_at', 'desc')
            ->getFirstItem();
        $data[]      = [
            'id'    => 'ticket.lastMessage',
            'label' => 'Last Message',
            'value' => $this->clear((string)$lastMessage->getBody()),
        ];

        return $data;
    }
}
