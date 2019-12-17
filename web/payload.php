<?php	 
				switch($payload)
				{		

					case CMD_ANEKDOT:
						sendmessage($user_id, anekdot());
						echo('OK');
						break;
					
					case CMD_WEATHER:
						sendmessage($user_id, weather());
						echo('OK');
						break;
						
					case CMD_MAIN:
						$kbd = [
							'one_time' => false,
							'buttons' => [
								[getBtn("Расписание", COLOR_PRIMARY, CMD_SCHEDULE)],
								[getBtn("Случайный анекдот", COLOR_POSITIVE, CMD_ANEKDOT), getBtn("&#127783; Погода", COLOR_POSITIVE, CMD_WEATHER)],
										]
							];
						sendmessage_kbd($user_id, 'Вы в главном меню', $kbd);
						echo('OK');
						break;
						
					case CMD_SCHEDULE:
						$kbd = [
						'one_time' => true,
						'buttons' => [
							[getBtn("СБС-701", COLOR_PRIMARY, CMD_SBS701), getBtn("СББ-701", COLOR_PRIMARY, CMD_SBB701), getBtn("СМБ-701", COLOR_PRIMARY, CMD_SMB701)],
							[getBtn("Главное меню", COLOR_DEFAULT, CMD_MAIN)]
						]];
						sendmessage_kbd($user_id, 'Выберите группу:', $kbd);
						echo('OK');
						break;
						
					/*__________________НИЖЕ МЯСО__________________*/
					case CMD_SBS701:
						$kbd = [
							'one_time' => false,
							'buttons' => [
							[getBtn("ПН", COLOR_PRIMARY, SBS_PN), getBtn("ВТ", COLOR_PRIMARY, SBS_VT), getBtn("СР", COLOR_PRIMARY, SBS_SR)],
							[getBtn("ЧТ", COLOR_PRIMARY, SBS_CT), getBtn("ПТ", COLOR_PRIMARY, SBS_PT), getBtn("СБ", COLOR_PRIMARY, SBS_SB)],
							[getBtn("Главное меню", COLOR_DEFAULT, CMD_MAIN)]
						]];
						sendmessage_kbd($user_id, 'Выберите день недели:', $kbd);
						echo('OK');
						break;
						
						
					case CMD_SBB701:
						$kbd = [
							'one_time' => false,
							'buttons' => [
							[getBtn("ПН", COLOR_PRIMARY, SBB_PN), getBtn("ВТ", COLOR_PRIMARY, SBB_VT), getBtn("СР", COLOR_PRIMARY, SBB_SR)],
							[getBtn("ЧТ", COLOR_PRIMARY, SBB_CT), getBtn("ПТ", COLOR_PRIMARY, SBB_PT), getBtn("СБ", COLOR_PRIMARY, SBB_SB)],
							[getBtn("Главное меню", COLOR_DEFAULT, CMD_MAIN)]
						]];
						sendmessage_kbd($user_id, 'Выберите день недели:', $kbd);
						echo('OK');
						break;
						
						
					case CMD_SMB701:
						$kbd = [
							'one_time' => false,
							'buttons' => [
							[getBtn("ПН", COLOR_PRIMARY, SMB_PN), getBtn("ВТ", COLOR_PRIMARY, SMB_VT), getBtn("СР", COLOR_PRIMARY, SMB_SR)],
							[getBtn("ЧТ", COLOR_PRIMARY, SMB_CT), getBtn("ПТ", COLOR_PRIMARY, SMB_PT), getBtn("СБ", COLOR_PRIMARY, SMB_SB)],
							[getBtn("Главное меню", COLOR_DEFAULT, CMD_MAIN)]
						]];
						sendmessage_kbd($user_id, 'Выберите день недели:', $kbd);
						echo('OK');
						break;	
						
					
					
					/*__________________СБС__________________*/
					case SBS_PN:
						sendmessage($user_id, schedule(2,'ПН'));
						break;
						
					case SBS_VT:
						sendmessage($user_id, schedule(2,'ВТ'));
						
						break;	
						
					case SBS_SR:
						sendmessage($user_id, schedule(2,'СР'));
						break;
						
					case SBS_CT:
						sendmessage($user_id, schedule(2,'ЧТ'));
						break;	
						
					case SBS_PT:
						sendmessage($user_id, schedule(2,'ПТ'));
						break;
						
					case SBS_SB:
						sendmessage($user_id, schedule(2,'СБ'));
						break;	
						
						
						
					/*__________________СББ__________________*/
					case SBB_PN:
						sendmessage($user_id, schedule(3,'ПН'));
						break;

					case SBB_VT:
						sendmessage($user_id, schedule(3,'ВТ'));
						break;

					case SBB_SR:
						sendmessage($user_id, schedule(3,'СР'));
						break;

					case SBB_CT:
						sendmessage($user_id, schedule(3,'ЧТ'));
						break;

					case SBB_PT:
						sendmessage($user_id, schedule(3,'ПТ'));
						break;

					case SBB_SB:
						sendmessage($user_id, schedule(3,'СБ'));
						break;						
					
			
						
					/*__________________СМБ__________________*/
					case SMB_PN:
						sendmessage($user_id, schedule(4,'ПН'));
						break;
						
					case SMB_VT:
						sendmessage($user_id, schedule(4,'ВТ'));
						break;
						
					case SMB_SR:
						sendmessage($user_id, schedule(4,'СР'));
						break;
					case SMB_CT:
						sendmessage($user_id, schedule(4,'ЧТ'));
						break;

					case SMB_PT:
						sendmessage($user_id, schedule(4,'ПТ'));
						break;

					case SMB_SB:
						sendmessage($user_id, schedule(4,'СБ'));
						break;						
						
						
					default:
						$kbd = [
							'one_time' => false,
							'buttons' => [
								[getBtn("Расписание", COLOR_PRIMARY, CMD_SCHEDULE)],
								[getBtn("Случайный анекдот", COLOR_POSITIVE, CMD_ANEKDOT), getBtn("&#127783; Погода", COLOR_POSITIVE, CMD_WEATHER)],
										]
							];
						sendmessage_kbd($user_id, "", $kbd);
						echo('OK');
						break;
				}
?>
