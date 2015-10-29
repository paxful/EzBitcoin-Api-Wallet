<?php

class ChangeAddressesSettingsSeeder extends Seeder {

	public function run()
	{
		// not to delete in production just in case lol
		if ( App::environment('production') )
		{
			DB::table('change_addresses')->delete();
			DB::table('settings')->delete();
		}

		Settings::create(['key' => 'monitor_outputs', 'value' => 1]);
		Settings::create(['key' => 'minimum_outputs_threshold', 'value' => 125]);
		Settings::create(['key' => 'outputs_to_add', 'value' => 125]);
		Settings::create(['key' => 'outputs_cache_duration', 'value' => 45]);

		if ( App::environment('production') )
		{
			// based on 15 send outs in a batch which is ~7 BTC in total that needs to be available in Bitcoin Core
			Settings::create(['key' => 'amount_to_add', 'value' => 6900000]);
		}
		else
		{
			// in non-prod environment make as little as possible cause we dont have much testcoins, 125 * 0.009 = 1.125 BTC
			Settings::create(['key' => 'amount_to_add', 'value' => 900000]); // 0.009 btc
		}

		if ( App::environment('production') )
		{
			// TODO add production change addresses
			ChangeAddress::create(['address' => 'xxx', 'user_id' => 2]);
			ChangeAddress::create(['address' => 'xxx', 'user_id' => 2]);
			ChangeAddress::create(['address' => 'xxx', 'user_id' => 2]);
			ChangeAddress::create(['address' => 'xxx', 'user_id' => 2]);
		}
		else
		{
			ChangeAddress::create(['address' => 'mtetXTNemFakFdCyPqntKHxkL66gr24a2j', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n3kx9hPwJGHroeR6qAYjAzfBS4KuFzKyEn', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mmfvEHNkZNTMZQdxgsejXMn45G54JicqKt', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mtetXTNemFakFdCyPqntKHxkL66gr24a2j', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mzMME4zTMmS47f9B5gtsztYCzzvXQxLAgm', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n37P7KYrgeZRwTEutBEEvZy6XbTGjX6RKV', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'msQqLA2xDfw5jbNF7mV1YbtnKGJD9HXeYD', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mnx3QYvDs7cMZ6qTb11npSzqVduNLRH1Re', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mpzSJE3wvougpdna84Q5157BCsd1Nx3ZgP', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mqBdV8Z57AsUDca2xPh7aEJzbAmoQKXys1', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mjRgNyAmNb1rGb4mw3QeBSMmUX7aUTgGk3', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mhzhQx97ASUgyfmGfS48XBj84AY3AcyLWo', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n1gZWKBwQJYxh74NbMGKzEvneeVdzSJZWF', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mzSaJhgDsptkPYSNR1sbvvB9yRjojPNZE1', 'user_id' => 1]);

			ChangeAddress::create(['address' => 'mfpp3XdPcKKHmtYPL46Ygkf78yU1yUkb9c', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mtFwj6QobpkNnGstHYQYMW8LpQvLwe9wqv', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n2B9DgAUubctjtW7DqAZsgR7Lh1q49Xt5Q', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'myUXwRQiKywvgZMbM1KYPKYZef57tWc1WG', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mofcMJRrrcAgQH9ZhAZqGN4FQLWGYRFRsM', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mq59AigXtnJTcSDTy3ApJR1qTt89RQ9dum', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n3JD3XrG2CfXJySc7g5HPEnwHMXxWkhawi', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'msxdPVFhwBq3TJ5hW7goL7t3bNPSxwAktU', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mqhPS6Q7FhW6UAbz8VrBf6mBLcPJZenRcc', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mtcNWFrthMhH7BpTn8Noi82K7xvgr1D4Di', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'my62tbtqABApeq776TdERazV3wTA17yW5u', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mteyT2SxazMAzQF75TgoxWerYLcof3VAap', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mno3bcDdX4J1fDrwp7pKPrXWFUDtUTx371', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mi4gzoedqMK3GzehxTPptFNAJYTKeAUdPq', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mzDqRN9LHGUfKn7XTb3bo3U99ySdDdoWt6', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mvLYxkLsEz4hLRFTtTvtLq86fusGhgSSJC', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mrdvEr2FHDB26qjTBq4B1etd7KEPjtCkJk', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n1U7XnJk9rdCoRMgm1MUUqwh1kTRHNvS8r', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mhTeh9gLuovXsoycbT4Dwrqi9HgfgjPRLB', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mjpsEvTkrVoWL2Fms4Rvmta5Xy4fwPrhSY', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mfYkZbGmeBqfoQKZbHn4FkD8SZofwgtKq9', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mt7GtWwZKctx6doZpTwH5Tnuar2tyqTsdA', 'user_id' => 1]);

			ChangeAddress::create(['address' => 'mwcpW3uh4gUYEJQbymHhYQsQe1UCrdnN7o', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mgiyBPCbsjyEyZuNxkEaikfNU9hiC1HfQ6', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mpNm4U3iUqzuS7kEM9sjrFCGTSUo3grgox', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mvt3shEcj8qcg5UbygEmJsM2Kiiqh3GqfJ', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mo6fmY7VWJLDug2YAGvwMqrAu61yb4WNt7', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mh2STFMUt6UTo56qnAK1QB9RsVs483YJJJ', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n151xk8aJsrL9EmPLTkxHtaFukT9Wdp5fX', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mqP9P7GvPCPqhdoAP5ADT4MzMKiAR9hpgm', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mzBCZQnNJyqfuvPBxwQNqp6hjytbnUwK3g', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mmckHjrNLD3eZbtx171hjAgyr8CzW9vxvR', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'moSDPVuqJompSK25EkTNqFGNSGyYKsmosq', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n1T1vH9HvbumY5DDq9QE3jF65UVuwUs41A', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mvLL9v6ctkycGdjutxXEU71SWqRyfKHAEP', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n4C93EMGERpXUfTwTDR17ByoWQGNKX5Aoh', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mmShcGi28RJdnqFM8BZDNpERa14Hfm1mSg', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mu1UUSoxYaLBk1A8aZNTydbV6XZF1HYqwT', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mhLm84RzVA2y3iFpGjF4u1gaGnb4CLD1dS', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mwnNAw7dLyLU36ZdWPqpZCujM3DY6UoJQW', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mggMSb8juVL4WcHAhhv5VXCxkuHB6XZjoU', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'muW1ZjWoGkjYaS9aVnoG827acVQgLSAmno', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n1fnBBxKwE75bGG2ejY7ZNvW8Cdq4JGycP', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mu3Aba5A6voq18MVPdibsCzTtENznFR3tH', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mtbofit8D4qdXyfuPGQuCAHcAHAsv1s741', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mziMYjPw4CaRv2BYStpp4w4m3RaReowb2N', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n1GZy9fAhyK4mdGXAm4hGZHGDTD5zeqCEx', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mn81EBGzsn9yzXYaFAc12pBgjWxotCsbCm', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mzhFid7V7VtvSGPYgUENCR2txNSDp38XLx', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n4WTF6q9JeJYYNJGrujzK9fF7hoF2npr1F', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mmufTKuiWVCd7DyE4cpS5TU35womztddhb', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'muwyPhLB5CKDHqQbGKBDcNSkWgTjk8XzvV', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mujgJufwjKxEZLGmVujDGxZYCEfhu1W8UK', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mvEh31owLeqmhqLPKCkevjifmhNkQ5yPV6', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mwaaMw7bTWY4nX8cxVEZ7EhnfAhPTzp1WG', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mmJZdF88zvvar8shr89V41RBzriWW5omhE', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mogpopFCbbBwkFR5cozqagZvPbjZPX73Fw', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mtMBseVpLjTYWngNKggQ7rqsDrojuFLB2i', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mgjxshvHTVdHHkHsEc73o4FpkZQ6YiYMLY', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'micjEJ66oJ9pMGduj1niYNovUScXDGy845', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n2hriLHs9F1268mogbP1Q6T5isqZ8hgVCE', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mkFSMspFNUMyVJDUKCBC1FXBZCDA7mYvMa', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mwbJf6wwp1hX3ZSD7qoimn6s6PVfW2UkKt', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n4gjmRBfQC4HYeMXn2gTyatpcxxtN8LM43', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mpZzbQP6ZYtMX7quWEKQ6eCZueVe9gTY42', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'muJnRQrkuvYHVwVumKieATkqiR69TiDSEX', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mptLxjgnkUb7FT7BWAmmoRKn8sCdnTzvsk', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mw1eWFsSqXmM6Wqd9i7RdqGDZo46Z6NzQ9', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mfhS5YuAVjREGJ7erZm6gaz6CW2uHdeo95', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'msRoLguTRdiYd39HdnEhB25i7ZQMYUdDAa', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'moEGF49NMXEKz8oR3inbmHwUF162XCzTEe', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'msoSmboB85F3GBMk14DF72EGXUUqHF6pVT', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'moebYhNeC2sFzfGEDaeC3rqS3CcpL16Tq3', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mnbq6rcSFM8KyY4SRhcrb9RRiygCorWdNZ', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mi7dHEzDuekiZJtZgk9wp5hwh4x7s3Krnp', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mnrUHMRZFgsGXyQXGAYEQp8GVQosXUzJzH', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n1E37dnzja4hUyDrDJLEPzDw3sfJJNkhyo', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mfmeL7PSNTeXaFcEKzsi4Xo5bzTzn7k1QZ', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mzr9vJ8K1GwK1eNghsW5dcufex7bJLjcZh', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mucUSW8HT3vXomRG6s5TkToFyHjtQx7QH2', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mw768DoPxc9Cbf4gxGZfZ3PM8vBUmepdM7', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mydeRWv5iV3ysi8hWUMgqxVBvf1srrvveK', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mxbYhyyNahgzkrs7QJay9iKgEuVxe4UcP3', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n26DpvpPMdqoFiiqKVnHZh7k22C5re7w5r', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mnHpz1z7bdbZ5aXv1LFpC19WWZdWTJadjn', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mfkHkhsbnDS3skpAANRKeBL8dv5nbeL612', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mypUDKFpGuCeyfoQUUbUPVj5Xr3c2ijqGf', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'myWM65UzP1r7penLqvnZnBe7A36ssFoyda', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mua9VYBpX8z5wzXbekJnFcbHpjYHedsWxe', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'muSZBiGLHdHDczGL7yV8TKRa9guNVYV8WN', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'myWM65UzP1r7penLqvnZnBe7A36ssFoyda', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mjZdDbz3Dq7RY4iJfJHG9PyMZz2C2eKe9r', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mj6EYrDpayWuE2ZpkRi1D4opayL9nWJepv', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mszjX8yHoeLn5cnW8EjBjcKaG6pZh9prPS', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mtmDj33YpVqJV4jDjVXw8pAXeGgh5D3pLr', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mnr9F2rUthy2SUePaR5Q7ronA6sV3LSePU', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mvtSZTHX6gbKf7WyuBKhSyc3KhBmp3UdmR', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mpvkk6LNxAF62Y1sZ8hYSqUQnuAgWmwaqF', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mxcyBqZq3BifNbCHTpQ3D3vrw3veA28wJz', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mrc6oiVUU4o7337vx4W1QXjJyhttJMJLVG', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'moVNzhjoJpafet2HxtFvsV9tFkHTjaSN28', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'my7GuScwY7FuFmVVtBP3udDXDZDKncoMSu', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mwLkb9FKdp9QS5DLPR2nKReKLoNn6m16rP', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mi9XAStreMN6saWARjedyF9tYaoETQNoku', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mumgtq1vHTfnkSjaMJWigz66CNVFPZXueU', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mvgjkTgK6xgsQb7MmFERRgJw37SiADEHPr', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mgzE6zDyFcHfHRdgjGGqjFqu9wJJC54jfo', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n1SE8GtHjRZRY4rGNoiVZYNXFG7jk3DBPE', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n1ZY8a55nwWDC6kqdXhLFP2ZKZXtAUs1Qw', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mzA7YNwws8WadQyWRuGewwTLq5sgqcpdeh', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mnztpXU4gaEAMmARZRJ7YkR3gusxyPXXYu', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mvU95ePWvE6YoDLbfY7KsR1FErrBddSSjk', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'mnL4c5DBiDmVmLhVHF5jnJ1VfUDvBjnzDH', 'user_id' => 1]);
			ChangeAddress::create(['address' => 'n4UqrLvZCoBigsMYt8AjyUzsjYLCH54SVf', 'user_id' => 1]);
		}
	}
}