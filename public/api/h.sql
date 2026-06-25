-- 1. 添加缺失的首充奖励字段
ALTER TABLE users ADD COLUMN first_recharge_done TINYINT(1) DEFAULT 0;
ALTER TABLE users ADD COLUMN last_daily_bonus_at DATE DEFAULT NULL;

-- 2. 把 status 从 INT 改为 VARCHAR，兼容字符串状态
ALTER TABLE users MODIFY COLUMN status VARCHAR(20) DEFAULT 'active';

-- 3. 更新现有数据：把旧数字状态转为字符串
UPDATE users SET status = 'active' WHERE status = '1' OR status = 1 OR status IS NULL;
UPDATE users SET status = 'frozen' WHERE status = '0' OR status = 0;

-- 4. 聊天索引优化
ALTER TABLE chat_messages ADD INDEX idx_chat_receiver (receiver_id);
