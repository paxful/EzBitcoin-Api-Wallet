-- ----------------------------
-- Sequence structure for addresses_id_seq
-- ----------------------------
DROP SEQUENCE "public"."addresses_id_seq";
CREATE SEQUENCE "public"."addresses_id_seq"
 INCREMENT 1
 MINVALUE 1
 MAXVALUE 9223372036854775807
 START 1
 CACHE 1;
SELECT setval('"public"."addresses_id_seq"', 2, true);

-- ----------------------------
-- Sequence structure for balances_id_seq
-- ----------------------------
DROP SEQUENCE "public"."balances_id_seq";
CREATE SEQUENCE "public"."balances_id_seq"
 INCREMENT 1
 MINVALUE 1
 MAXVALUE 9223372036854775807
 START 1
 CACHE 1;
SELECT setval('"public"."balances_id_seq"', 1, true);

-- ----------------------------
-- Sequence structure for invoice_addresses_id_seq
-- ----------------------------
DROP SEQUENCE "public"."invoice_addresses_id_seq";
CREATE SEQUENCE "public"."invoice_addresses_id_seq"
 INCREMENT 1
 MINVALUE 1
 MAXVALUE 9223372036854775807
 START 1
 CACHE 1;
SELECT setval('"public"."invoice_addresses_id_seq"', 183, true);

-- ----------------------------
-- Sequence structure for logs_id_seq
-- ----------------------------
DROP SEQUENCE "public"."logs_id_seq";
CREATE SEQUENCE "public"."logs_id_seq"
 INCREMENT 1
 MINVALUE 1
 MAXVALUE 9223372036854775807
 START 1
 CACHE 1;
SELECT setval('"public"."logs_id_seq"', 444, true);

-- ----------------------------
-- Sequence structure for payout_history_id_seq
-- ----------------------------
DROP SEQUENCE "public"."payout_history_id_seq";
CREATE SEQUENCE "public"."payout_history_id_seq"
 INCREMENT 1
 MINVALUE 1
 MAXVALUE 9223372036854775807
 START 1
 CACHE 1;

-- ----------------------------
-- Sequence structure for transactions_id_seq
-- ----------------------------
DROP SEQUENCE "public"."transactions_id_seq";
CREATE SEQUENCE "public"."transactions_id_seq"
 INCREMENT 1
 MINVALUE 1
 MAXVALUE 9223372036854775807
 START 87
 CACHE 1;
SELECT setval('"public"."transactions_id_seq"', 87, true);

-- ----------------------------
-- Sequence structure for users_id_seq
-- ----------------------------
DROP SEQUENCE "public"."users_id_seq";
CREATE SEQUENCE "public"."users_id_seq"
 INCREMENT 1
 MINVALUE 1
 MAXVALUE 9223372036854775807
 START 1
 CACHE 1;

-- ----------------------------
-- Table structure for addresses
-- ----------------------------
DROP TABLE IF EXISTS "public"."addresses";
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
"server" varchar(24) COLLATE "default"
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Table structure for balances
-- ----------------------------
DROP TABLE IF EXISTS "public"."balances";
CREATE TABLE "public"."balances" (
"id" int4 DEFAULT nextval('balances_id_seq'::regclass) NOT NULL,
"user_id" int4 NOT NULL,
"crypto_type" varchar(48) COLLATE "default" NOT NULL,
"balance" int8 NOT NULL,
"total_received" int8 NOT NULL
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Table structure for invoice_addresses
-- ----------------------------
DROP TABLE IF EXISTS "public"."invoice_addresses";
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
"received_amount" int8 DEFAULT 0 NOT NULL
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Table structure for logs
-- ----------------------------
DROP TABLE IF EXISTS "public"."logs";
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
"date_created" timestamp(6) DEFAULT timezone('utc'::text, now()) NOT NULL
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Table structure for payout_history
-- ----------------------------
DROP TABLE IF EXISTS "public"."payout_history";
CREATE TABLE "public"."payout_history" (
"id" int8 DEFAULT nextval('payout_history_id_seq'::regclass) NOT NULL,
"crypto_amount" int8,
"crypto_type" varchar(12) COLLATE "default",
"tx_id" varchar(100) COLLATE "default",
"address_to" varchar(48) COLLATE "default",
"confirmations" int4,
"log_id" int8,
"date_created" timestamp(6) DEFAULT timezone('utc'::text, now())
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Table structure for transactions
-- ----------------------------
DROP TABLE IF EXISTS "public"."transactions";
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
"date_created" timestamp(6) DEFAULT timezone('utc'::text, now())
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS "public"."users";
CREATE TABLE "public"."users" (
"id" int4 DEFAULT nextval('users_id_seq'::regclass) NOT NULL,
"guid" varchar(48) COLLATE "default",
"password" varchar(48) COLLATE "default",
"email" varchar(100) COLLATE "default",
"name" varchar(100) COLLATE "default",
"callbackurl" text COLLATE "default",
"secret" varchar(48) COLLATE "default",
"lastactivity" timestamp(6),
"created" timestamp(6) DEFAULT timezone('utc'::text, now()) NOT NULL
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Alter Sequences Owned By 
-- ----------------------------
ALTER SEQUENCE "public"."addresses_id_seq" OWNED BY "addresses"."id";
ALTER SEQUENCE "public"."balances_id_seq" OWNED BY "balances"."id";
ALTER SEQUENCE "public"."invoice_addresses_id_seq" OWNED BY "invoice_addresses"."id";
ALTER SEQUENCE "public"."logs_id_seq" OWNED BY "logs"."id";
ALTER SEQUENCE "public"."payout_history_id_seq" OWNED BY "payout_history"."id";
ALTER SEQUENCE "public"."transactions_id_seq" OWNED BY "transactions"."id";
ALTER SEQUENCE "public"."users_id_seq" OWNED BY "users"."id";

-- ----------------------------
-- Primary Key structure for table addresses
-- ----------------------------
ALTER TABLE "public"."addresses" ADD PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table balances
-- ----------------------------
ALTER TABLE "public"."balances" ADD PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table invoice_addresses
-- ----------------------------
ALTER TABLE "public"."invoice_addresses" ADD PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table logs
-- ----------------------------
ALTER TABLE "public"."logs" ADD PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table payout_history
-- ----------------------------
ALTER TABLE "public"."payout_history" ADD PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table transactions
-- ----------------------------
ALTER TABLE "public"."transactions" ADD PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table users
-- ----------------------------
ALTER TABLE "public"."users" ADD PRIMARY KEY ("id");

-- ----------------------------
-- Foreign Key structure for table "public"."invoice_addresses"
-- ----------------------------
ALTER TABLE "public"."invoice_addresses" ADD FOREIGN KEY ("log_id") REFERENCES "public"."logs" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION;
