-- ----------------------------
--  Table structure for "public"."addresses"
-- ----------------------------
DROP TABLE "public"."addresses";
CREATE TABLE "public"."addresses" (
"id" int8 DEFAULT nextval('addresses_id_seq'::regclass) NOT NULL,
"address" varchar(48) COLLATE "default",
"label" text COLLATE "default",
"user_id" int4,
"balance" int8 DEFAULT 0 NOT NULL,
"crypto_type" varchar(8) COLLATE "default",
"created" timestamp(6) DEFAULT timezone('utc'::text, now()) NOT NULL,
"total_received" int8 DEFAULT 0 NOT NULL,
"previous_balance" int8 DEFAULT 0 NOT NULL,
"server" varchar(24) COLLATE "default",
PRIMARY KEY ("id")
)
WITH (OIDS=FALSE)
;;

-- ----------------------------
--  Table structure for "public"."balances"
-- ----------------------------
DROP TABLE "public"."balances";
CREATE TABLE "public"."balances" (
"id" int4 DEFAULT nextval('balances_id_seq'::regclass) NOT NULL,
"user_id" int4 NOT NULL,
"crypto_type" varchar(48) COLLATE "default" NOT NULL,
"balance" int8 NOT NULL,
"total_received" int8 NOT NULL,
PRIMARY KEY ("id")
)
WITH (OIDS=FALSE)
;;

-- ----------------------------
--  Table structure for "public"."invoice_addresses"
-- ----------------------------
DROP TABLE "public"."invoice_addresses";
CREATE TABLE "public"."invoice_addresses" (
"id" int8 DEFAULT nextval('invoice_addresses_id_seq'::regclass) NOT NULL,
"address" varchar(48) COLLATE "default",
"destination_address" varchar(48) COLLATE "default",
"label" text COLLATE "default",
"invoice_amount" int8 DEFAULT 0 NOT NULL,
"transaction_hash" varchar(100) COLLATE "default",
"input_transaction_hash" varchar(100) COLLATE "default",
"crypto_type" varchar(8) COLLATE "default",
"created" timestamp(6) DEFAULT timezone('utc'::text, now()) NOT NULL,
"callback_url" text COLLATE "default",
"received" int2 DEFAULT 0 NOT NULL,
"forward" int2 DEFAULT 0 NOT NULL,
"log_id" int8 NOT NULL,
"received_amount" int8 DEFAULT 0 NOT NULL,
PRIMARY KEY ("id"),
FOREIGN KEY ("log_id") REFERENCES "public"."logs" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION
)
WITH (OIDS=FALSE)
;;

-- ----------------------------
--  Table structure for "public"."logs"
-- ----------------------------
DROP TABLE "public"."logs";
CREATE TABLE "public"."logs" (
"id" int8 DEFAULT nextval('logs_id_seq'::regclass) NOT NULL,
"method" varchar(48) COLLATE "default",
"guid" varchar(48) COLLATE "default",
"ipaddress" varchar(40) COLLATE "default",
"querystring" text COLLATE "default",
"agent" text COLLATE "default",
"referrer" text COLLATE "default",
"response" text COLLATE "default",
"server" varchar(255) COLLATE "default",
"date_created" timestamp(6) DEFAULT timezone('utc'::text, now()) NOT NULL,
PRIMARY KEY ("id")
)
WITH (OIDS=FALSE)
;;

-- ----------------------------
--  Table structure for "public"."payout_history"
-- ----------------------------
DROP TABLE "public"."payout_history";
CREATE TABLE "public"."payout_history" (
"id" int8 DEFAULT nextval('payout_history_id_seq'::regclass) NOT NULL,
"crypto_amount" int8,
"crypto_type" varchar(12) COLLATE "default",
"tx_id" varchar(100) COLLATE "default",
"address_to" varchar(48) COLLATE "default",
"confirmations" int4,
"log_id" int8,
"date_created" timestamp(6) DEFAULT timezone('utc'::text, now()),
PRIMARY KEY ("id")
)
WITH (OIDS=FALSE)
;;

-- ----------------------------
--  Table structure for "public"."transactions"
-- ----------------------------
DROP TABLE "public"."transactions";
CREATE TABLE "public"."transactions" (
"id" int8 DEFAULT nextval('transactions_id_seq'::regclass) NOT NULL,
"tx_id" varchar(100) COLLATE "default",
"user_id" int4,
"address_to" varchar(48) COLLATE "default",
"address_from" varchar(48) COLLATE "default",
"account_to" varchar(100) COLLATE "default",
"account_from" varchar(100) COLLATE "default",
"crypto_amount" int8,
"crypto_type" varchar(12) COLLATE "default",
"confirmations" int4,
"created" timestamp(6) DEFAULT timezone('utc'::text, now()) NOT NULL,
"date_updated" timestamp(6),
"response_callback" text COLLATE "default",
"callback_status" int4,
"callback_url" text COLLATE "default",
"block_hash" text COLLATE "default",
"block_index" int4,
"block_time" int4,
"tx_time" int4,
"tx_timereceived" int4,
"tx_category" varchar(24) COLLATE "default",
"address_account" varchar(100) COLLATE "default",
"balance" numeric DEFAULT 0,
"previous_balance" int8 DEFAULT 0,
"crypto_bitcoindbalance" int8 DEFAULT 0,
"credit" int8 DEFAULT 0,
"debit" int8 DEFAULT 0,
"messagetext" varchar(256) COLLATE "default",
"label" varchar(100) COLLATE "default",
"label2" varchar(100) COLLATE "default",
"label3" varchar(100) COLLATE "default",
"transaction_type" varchar(25) COLLATE "default",
"server" varchar(24) COLLATE "default",
"log_id" int8 NOT NULL,
"date_created" timestamp(6) DEFAULT timezone('utc'::text, now()),
PRIMARY KEY ("id")
)
WITH (OIDS=FALSE)
;;

-- ----------------------------
--  Table structure for "public"."users"
-- ----------------------------
DROP TABLE "public"."users";
CREATE TABLE "public"."users" (
"id" int4 DEFAULT nextval('users_id_seq'::regclass) NOT NULL,
"guid" varchar(48) COLLATE "default",
"password" varchar(48) COLLATE "default",
"email" varchar(100) COLLATE "default",
"name" varchar(100) COLLATE "default",
"callbackurl" text COLLATE "default",
"secret" varchar(48) COLLATE "default",
"lastactivity" timestamp(6),
"created" timestamp(6) DEFAULT timezone('utc'::text, now()) NOT NULL,
PRIMARY KEY ("id")
)
WITH (OIDS=FALSE)
;;