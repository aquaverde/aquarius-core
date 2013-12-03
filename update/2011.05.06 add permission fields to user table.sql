-- Add some more permission fields
ALTER TABLE `users`
ADD delete_permission TINYINT(1) NOT NULL DEFAULT 1,
ADD copy_permission TINYINT(1) NOT NULL DEFAULT 1;
