

INSERT INTO `payment_types` (`id`, `visible_in_customer`, `title`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Wire', 'This is wire description', 0, NULL, '2020-06-04 13:25:20'),
(2, 1, 'Cheque', 'This is cheque description', 0, NULL, '2020-06-04 13:25:28'),
(3, 1, 'Cash', 'This is cash description', 0, NULL, '2020-06-04 13:31:45'),
(5, NULL, 'Credit Note', 'Credit Note', 0, '2020-04-20 15:14:47', '2020-04-20 15:14:47'),
(6, NULL, 'Saving-BBL SA', 'Transfer via BBL account', 0, '2020-04-20 15:15:16', '2020-06-29 14:37:22'),
(7, NULL, 'Saving-KBank SA', 'Transfer via KBank account', 0, '2020-04-20 15:51:32', '2020-09-02 16:53:54'),
(8, NULL, 'Exchange Gain/Loss', 'this is exchange gain/loss description', 0, '2020-04-29 17:54:54', '2020-04-29 17:54:54'),
(9, 0, 'Current-KBank CA', 'Saving-KBank CA', 0, '2020-06-29 14:36:32', '2020-09-02 16:54:05'),
(10, 0, 'Saving-SCB SA', 'Saving-SCB SA', 0, '2020-07-14 12:07:28', '2020-07-14 12:07:28');
