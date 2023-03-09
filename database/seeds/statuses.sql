INSERT INTO `statuses` (`id`, `title`, `parent_id`, `prefix`, `counter_formula`, `reset`, `counter`, `print_1`, `print_2`, `is_texica`, `created_at`, `updated_at`) VALUES
(1, 'Quotation', 0, 'QU', 'ym-####', 'Monthly', NULL, 'quotation-invoice', 'quotation-invoice-inc-vat', 0, '2019-11-01 02:02:57', '2019-11-01 02:02:57'),
(2, 'Draft', 0, 'DR', 'ym-####', 'Monthly', NULL, NULL, NULL, 0, '2019-11-01 02:03:13', '2019-11-01 02:03:13'),
(3, 'Invoice', 0, 'IN', 'ym-####', 'Monthly', NULL, 'invoice-inc-vat', 'proforma-print', 0, '2019-11-01 02:03:32', '2019-11-01 02:03:32'),
(4, 'Purchase Order', 0, 'PO', 'ym-####', 'Monthly', NULL, 'invoice', NULL, 0, '2019-11-01 02:03:47', '2019-11-01 02:03:47'),
(5, 'Unfinished Quotation', 1, 'DRQ', '#', 'Never', NULL, NULL, NULL, 0, '2019-11-01 02:04:31', '2019-11-01 02:04:31'),
(6, 'Waiting Confirmation', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2019-11-01 02:04:51', '2019-11-01 02:04:51'),
(7, 'Waiting Gen PO', 2, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2019-11-01 02:08:15', '2019-11-01 02:08:15'),
(8, 'Purchasing', 2, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2019-11-01 02:08:29', '2019-11-01 02:08:29'),
(9, 'Importing', 2, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2019-11-01 02:08:41', '2019-11-01 02:08:41'),
(10, 'Waiting To Pick', 2, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2019-11-01 02:08:54', '2019-11-01 02:08:54'),
(11, 'Complete', 3, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2019-11-01 02:09:30', '2019-11-01 02:09:30'),
(12, 'Waiting Confirm', 4, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2019-11-01 02:09:48', '2019-11-01 02:09:48'),
(13, 'Shipping', 4, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2019-11-01 02:10:04', '2019-11-01 02:10:04'),
(14, 'Dispatch From Supplier', 4, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2019-11-01 02:10:20', '2019-11-01 02:10:20'),
(15, 'Received Into Stock', 4, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2019-11-01 02:10:38', '2019-11-01 02:10:38'),
(16, 'Draft PO', 4, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2019-12-31 00:00:00', '2020-07-27 19:31:41'),
(17, 'Cancelled', 0, NULL, NULL, NULL, NULL, NULL, 'invoice', 0, '2019-12-31 00:00:00', '2019-12-31 00:00:00'),
(18, 'Cancelled', 17, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2019-12-31 00:00:00', '2019-12-31 00:00:00'),
(19, 'Transfer Document', 0, 'TD', 'ym-####', 'Monthly', NULL, NULL, NULL, 0, '2020-03-30 02:03:47', '2020-03-30 02:03:47'),
(20, 'Waiting Confirmation', 19, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-03-29 19:00:00', '2020-03-29 19:00:00'),
(21, 'Waiting Transfer', 19, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-03-29 19:00:00', '2020-03-29 19:00:00'),
(22, 'Complete Transfer', 19, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-03-29 19:00:00', '2020-03-29 19:00:00'),
(23, 'Un-Finished TD', 19, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-03-29 19:00:00', '2020-03-29 19:00:00'),
(24, 'Paid Invoice', 3, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-03-31 00:00:00', '2020-03-31 00:00:00'),
(25, 'Credit Note', 0, 'CN', 'ym-##', NULL, NULL, 'credit-note-print', NULL, 0, '2020-06-10 00:00:00', '2020-06-10 00:00:00'),
(26, 'Waiting Confirmation', 25, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-06-10 00:00:00', '2020-06-10 00:00:00'),
(27, 'Complete', 25, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-06-10 00:00:00', '2020-06-10 00:00:00'),
(28, 'Debit Note', 0, 'DN', 'ym-##', NULL, NULL, NULL, NULL, 0, '2020-06-10 00:00:00', '2020-06-10 00:00:00'),
(29, 'Waiting Confirmation', 28, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-06-10 00:00:00', '2020-06-10 00:00:00'),
(30, 'Complete', 28, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-06-10 00:00:00', '2020-06-10 00:00:00'),
(31, 'Paid', 25, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(32, 'Partial Paid', 3, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(33, 'Partial Paid', 25, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(34, 'On Hold', 2, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(35, 'Ready To Pick', 2, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(36, 'Ready To Pick With Invoice', 2, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL),
(37, 'Manual Order', 0, '', '', '', NULL, '', '', 0, '2019-11-01 02:03:32', '2019-11-01 02:03:32'),
(38, 'Complete', 37, '', '', '', NULL, '', '', 0, '2019-11-01 02:03:32', '2019-11-01 02:03:32'),
(39, 'Manual PO', 0, '', '', '', NULL, '', '', 0, '2019-11-01 02:03:32', '2019-11-01 02:03:32'),
(40, 'Received Into Stock', 39, '', '', '', NULL, '', '', 0, '2019-11-01 02:03:32', '2019-11-01 02:03:32'),
(41, 'Account Receivables', 0, 'RE', 'ym-####', 'Monthly', NULL, '', '', 0, '2019-11-01 02:03:32', '2019-11-01 02:03:32');
