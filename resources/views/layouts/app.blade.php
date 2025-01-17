<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Welcome' }}</title>
</head>

<body>
<div id="wrapper" dir="ltr"
	style="background-color: #fdfdfd; margin: 0; padding: 70px 0; width: 100%; padding-top: 70px; padding-bottom: px; -webkit-text-size-adjust: none;"
	bgcolor="#fdfdfd" width="100%">
	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
		<tbody>
            <tr>
				<td align="center" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container"
						style="background-color: #fff;border: none;border-width: 1px;border-right-width: px;border-bottom-width: px;border-left-width: px;border-color: #dedede;border-radius: 3px;box-shadow: 0 1px 4px 1px rgba(0,0,0,.1);"
						bgcolor="#fff">
						<tbody>
							<tr>
								<td align="center" valign="top">
									<!-- Body -->
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
										<tbody>
                                            @include('components.header')
											<tr>
												<td valign="top" id="body_content"
													style="background-color: #fff; padding-top: px; padding-bottom: 0px;"
													bgcolor="#fff">
													<!-- Content -->
													<table border="0" cellpadding="20" cellspacing="0" width="100%">
														<tbody>
															<tr>
																<td valign="top"
																	style="padding: 48px 48px 32px; padding-left: 48px; padding-right: 48px;">
																	<div id="body_content_inner"
																		style="color: #333; text-align: left; font-size: 14px; line-height: 24px; font-family: &quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif; font-weight: 400;"
																		align="left">
																		{!! $body !!}
																	</div>
																</td>
															</tr>
														</tbody>
													</table>
													<!-- End Content -->
												</td>
											</tr>
										</tbody>
									</table>
									<!-- End Body -->
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
            @include('components.footer')

             <!-- <p style="text-align: center; color: #666;">
                © {{ date('Y') }}, Transbunnies. All Rights Reserved.
            </p> -->
        </tbody>
	</table>
</div>
</body>

</html>