<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\JWX;

use SocialConnect\JWX\JWK;

class JWKTest extends AbstractTestCase
{
    public function testFromRSA256PublicKey()
    {
        $jwk = JWK::fromRSAPublicKeyFile(__DIR__ . '/assets/rs256.key.pub');

        parent::assertSame(
            [
                'kty' => 'RSA',
                'n' => 'w9p2cmLLIxIH_RloYqx3JbgWp1k4SIGU1yC7wbqRI7LELTTLifKhbyj9XuR2lVDxkIFGRizWnc6iZVZTRunHZQjwp537gub4Q6wK_pFu_rfdPC6AVHBw6Vo8hpEzrmPHwiPLqnwQuOLFD82oCAaSiB2FBMjyzF_kFNR0b7301q_gLP-5szQ4gGnfrmP0bIg_OxLlCxxXP1U232o_rfEvMfdMQGMHInX_m3IJ_kXqwkUlPzms2bictjnRvOX7-763qfoYUMlEe-5Ovghl-gWQzWP5r-21nuD9zLeid5j-9bznaI0G69iSYgw833PQFnbcwdxrs-Ixjlbmyp0op5b_IQ',
                'e' => 'AQAB',
                'alg' => 'RSA256',
            ],
            $jwk->toArray()
        );
    }

    public function testFromRSA384PublicKey()
    {
        $jwk = JWK::fromRSAPublicKeyFile(__DIR__ . '/assets/rs384.key.pub');

        parent::assertSame(
            [
                'kty' => 'RSA',
                'n' => 'vSfQKp95P9KlFosHmcjB7ZnwGv9S4Q_Jsflc6AVH1D8bUnB4Jfn4fxfWx7Wz4-a-9ulZtiJf7Fk9Pe3Qcel3z5fFEIGNqGDRgpMnHh93DbYGEW0XokS6ATGF_ijm87lC1xvjGIYB07o03gupKykHjQkX5PtA6pkOgzF1Pe4cxDcro_73R8_Kzi6FSsLfsrmE_kJBW63lV4FOnqKnFZw2Uw7Bc6UT0lmVyZuMr_QRK_H5A-ZJTA6ChgkGXepRIgbqA1xaiDZLnj7EZahxQMaWqygzlRKwkKCm3XT0C9jHoY9gW_PW_ZhO-pdFL1Wit8H_zE9oKGxpoNbRJZMjG4Vej5tF7bmHv9fi1RkSQiR_dQIy0PxjWWXQu1CFvHvFJiZv1rlHQcw5jPmjUxeTY2wdjR7mDTqkWs6qTKb3aQY3n_ITv-pnQs8FnDWOeTSCw47I8ZBd1bPXhy2Yp1h_vaLZQZACDWaMggp0uxpldbZcGykMdtmVAPwjBYmOEwBqYANt',
                'e' => 'AQAB',
                'alg' => 'RSA384',
            ],
            $jwk->toArray()
        );
    }

    public function testFromRSA512PublicKey()
    {
        $jwk = JWK::fromRSAPublicKeyFile(__DIR__ . '/assets/rs512.key.pub');

        parent::assertSame(
            [
                'kty' => 'RSA',
                'n' => 'tUhEJVPAIwGPIsyh7cYnKx809s7XnkUoG7D6T092L_URRf1kDHL6_2amwxG_HeFtqdT_J9oy6SU8C5qdqfXJcR75hTZ_pMcIna496tqGgWHOGHnRUZfXMRufuCfCX9i-RnEXAdE8z-dHZpKtRnU3rp9wmfiECzHipmbwzf445G_iJtWkenQbHt1_Budsjb8JdAitdRuZtOvreqFLh5aPL5DHhE1tT-RNIiDEbgyi4W49HtYw_m7MbzQfarM6ZumYY3NJiCEBeK7PcCNiW1F-KrNBONZ_d2pJ67XY6CodcBM8I_tdnUYgoKIydJGRKfD6bzyzanfCOBLOn56_et1BiBHpK3MfdUuxMRn4W2NP6hbwyLBH-Dzd_CWui0F4jOemZEEYNLZYFMHYqYVTdTZZ7RMXeQSd0iEbgEq1NACDC-EJLqfmhIAgC_OO9cMmAARCUKsgzvy_RlYb51Gn5FQx_3BsmzFuYDAI4z_NyR18OzaeupGyMpr0LHMGXQ1f8rbs7L-pfUZYkfNoiRr-UWyZGOZ5r6Vlzi_PdJNDDlWq2WLk6H3l_XF9pif3_LoAy-a0FKwdzwGjUgOtWmdU1eXXeQLlVXw05HTNLWU9WnyJn9wkD2FK7SEx3IVjrnVn_-P5TnfkaKG2qgPu4YWRiYatPpZiZgGdvKYf3q49Qm9Hhps',
                'e' => 'AQAB',
                'alg' => 'RSA512',
            ],
            $jwk->toArray()
        );
    }

    public function testFromRSA256PrivateKey()
    {
        $jwk = JWK::fromRSAPrivateKeyFile(__DIR__ . '/assets/rs256.key');

        parent::assertSame(
            [
                'kty' => 'RSA',
                'n' => 'w9p2cmLLIxIH_RloYqx3JbgWp1k4SIGU1yC7wbqRI7LELTTLifKhbyj9XuR2lVDxkIFGRizWnc6iZVZTRunHZQjwp537gub4Q6wK_pFu_rfdPC6AVHBw6Vo8hpEzrmPHwiPLqnwQuOLFD82oCAaSiB2FBMjyzF_kFNR0b7301q_gLP-5szQ4gGnfrmP0bIg_OxLlCxxXP1U232o_rfEvMfdMQGMHInX_m3IJ_kXqwkUlPzms2bictjnRvOX7-763qfoYUMlEe-5Ovghl-gWQzWP5r-21nuD9zLeid5j-9bznaI0G69iSYgw833PQFnbcwdxrs-Ixjlbmyp0op5b_IQ',
                'e' => 'AQAB',
                'd' => 'mWWIE_sw410CCMhXq8Es6MwQYi5NGOz1KLGonQmFGBKx-D47lOYGbswJ9sK15ikpqma2JcyEo8DuDLTaMNZ1p7qi0oW4MkS4-jfLvKsn5jUYAETjmj8fEIXule8wLUxVbsceg378kfJ7Ke-HxhFvv1BvmNnS4SPRvkbQk5ySIXrr5wIXRAwAR9DtvGeJ-FbBHuymekwMww3QIrkxz7RRPZhkKNHNqFB85sNqVObGKiJ-uYnFaJW9_WKxWiWVK5B4sQTOfvstVYQn_EvIKON9fgV9C2vdp7-OE1k9Xq06SbHeczOy-D95McZPIdurKu9l_9ZX8558MlsfEFXq9rMmKQ',
                'p' => '6YTDvaSyQHKR09R0Dx_AmQyoOpXk7297Zs7vX4p6QM6e49cQ4bcDtBe2kWpsLkze2H9LZZlOTZd5cGMrkvQu-nbovgCa_AqNfeRJAx04pVMV5YteFfbNcVbrj7lavbMH6E8dDwDUG4C5m3Clzr3j4kPhpUlZ5tpY9jzCotxV34M',
                'q' => '1rVpMD5Udn5ztAwu4kc4al_xKtZRNvXfFVsS7UK-mDKFajDavKpEJ_LZPW0X6bgY0n1v54r4YB8OqvWbkm2-a1R0nPmaLW2SBfvAnuqqwTKSfVnkHR6y-YnmSHeW71gNUk2BIkz5-9ioQja5HPkLOn5RKCNhgFARrrQ7M17CYYs',
                'dp' => 'G1q3BzcMvmntVTAU7FSe3g7SghJfAAFCJlflSH7TNVY-3Jer3ZTvtR_1_fDGfWH51MiMj3k25_Xvfs_PIebCvgpB10gA37dova-JMfkxUoR6EyqROedwR2-UJoDi9UfMjFUAJWrGbfZVR7UZZy9tS2sCOrdt9ZHsS-PwNN20nXs',
                'dq' => 's93pruaob1PjrfN-20T6t_KD4HUGOFqldgiDxItji7DXH2yp8d9ZlXXWE6VuoPb-pGc89eXvyOZ7rTBwEa0qFlP8FPzs8h2WdLjAVuEUByFMowJJHTP4jx-88PxuTzeegVI4WfnOefK4ki-xx9nCVFA5wLxTE-D_zzFhXtmFUZ0',
                'qi' => 'qD7ZDOAR29bxrS5XqjYxAkD3GdIvoBS0kjdnN72JLyOk0u-ZaRQmqQIub09yKSChmUc0kRUjVNmev3n-GdsGICSzjIaTBlMkXd0uFMK8xe_H4M8waCoPUbPqxp38bZxalN0ENVSRd7wxk4wfT3_ZEhN_4ljZT0MI0F896B4P5TE',
                'alg' => 'RSA256',
            ],
            $jwk->toArray()
        );
    }

    public function testFromRSA384PrivateKey()
    {
        $jwk = JWK::fromRSAPrivateKeyFile(__DIR__ . '/assets/rs384.key');

        parent::assertSame(
            [
                'kty' => 'RSA',
                'n' => 'vSfQKp95P9KlFosHmcjB7ZnwGv9S4Q_Jsflc6AVH1D8bUnB4Jfn4fxfWx7Wz4-a-9ulZtiJf7Fk9Pe3Qcel3z5fFEIGNqGDRgpMnHh93DbYGEW0XokS6ATGF_ijm87lC1xvjGIYB07o03gupKykHjQkX5PtA6pkOgzF1Pe4cxDcro_73R8_Kzi6FSsLfsrmE_kJBW63lV4FOnqKnFZw2Uw7Bc6UT0lmVyZuMr_QRK_H5A-ZJTA6ChgkGXepRIgbqA1xaiDZLnj7EZahxQMaWqygzlRKwkKCm3XT0C9jHoY9gW_PW_ZhO-pdFL1Wit8H_zE9oKGxpoNbRJZMjG4Vej5tF7bmHv9fi1RkSQiR_dQIy0PxjWWXQu1CFvHvFJiZv1rlHQcw5jPmjUxeTY2wdjR7mDTqkWs6qTKb3aQY3n_ITv-pnQs8FnDWOeTSCw47I8ZBd1bPXhy2Yp1h_vaLZQZACDWaMggp0uxpldbZcGykMdtmVAPwjBYmOEwBqYANt',
                'e' => 'AQAB',
                'd' => 'Rwq67icy_Lt6cXsKAcIaw8g7G4ilcg3h7MwBDstc7OQ-uLmxBmJZ6DHl4t_ljkTNmCKQJQ3IBRaHH8k_rmjHLNqNkuN1drXWOjpWSMP8jNO-d7EHXVR-n5AgCRMHmqYL6op4wm8iJIkc7gBnKuSgB2JQ7RlIilOt1awvonDZsQAfjdpmuTvbqZBjU27ZYWC4CF6N-YbYSgMwqffg1Qb0iEFUesCXLzuiPDQFpNf_0wdwRPyqrrwMXZbqIz-r9SGvAQRbwlum0-lVhZafTyWsol1cYA11a0-x9vXaU8VRnFeiNum7IdKYDs--jOhJSu5tsL9k2Xc0gM7egXWANVNelOS1uJ_TyMVrms0K15Oy7MwurJM3R_vRJJv7dK0_Ldxnm0aXBslYvjxZTPxLCsYOEiQTsFcKvce76tV-IaXhUn4OQ1GoKjW6Xd9yNH3wG5M0oT_r3FltSy6cg4UcC6Xc5QK_Azs1kebbzTKlA707-loxFqvDLYbfhqx914zfW-9B',
                'p' => '3GUHx_2bSdkQU-Ns1lAzT3ott5xZPGF3FNqnDyV2arUzOwD16aLGlpF01kibXTar2Mlw717hs39cbX31p3xrb4fwdqc6UpHQ19u8twzs1uCVyP705TnouOiDCB05KeMZSfnDuqTi2n7nKaQVkZlk3y6Fa2bCGvjJ6-tChyql9SyWeK9_64EYd1LPGCtRrMHFmn-jbwZxVyJgxNHW8Tq12JsGu7h9ijll3Y6Huwc_HqI7XhaTHPpN5hpA_LgMwsI1',
                'q' => '27bPjOk8nggf9JrYDKOSDmydyDnbWJp9krrHXUNfLOV3LNj7NXj_VoZZ1jk_-4xXOcHwjrt4fhUrdDVcRKIsDixPRfbx1URcVI5hFN-XcDt-3JQ9zuURC7FVgdYCam4sWPjRX1YaxfrFYj4YNzJV6dhFS4ls1sjbq7-WGv-WKAA-rFhtCBLoTTNJ9QORty9b3XoNv6Fr4X_lLZSfGOTF9kiqIeBjk10C7Mn56SwMSZKIndFnQxwNRRWvL1c1UGNZ',
                'dp' => 'qHYZIob5IdNnJZga4x3eeoubUlOR6hNd4HTZaTxrbVkf1aQAVBt1zDVWp0xMZU2awVQInQ9bWcaqMY8sLF9wB4pTBX8Bl4eZqMVvVEPu7LgyBbbMHkLLXjtrnNIplZjfBaqCL7JFLFn_-9ZOHkKv1eBLXzLmf5NXVJs4-PRicisowQr0rmC5AMwtO_4wqepbTqLtm7nC_KVsbkUvFKiZwv2MggSdAQCqmlWN7im114aN9ncu7-FrlczPi9xq9JtZ',
                'dq' => '0sGUa9WbSPgANzGFRvJsexujalpdVquzAtnZvVOP61Adtk5ZAh0TyVrMuBpojpI7ZXLsnu2jkkYaTbmVzVxGqD3GuRU9Otb9PjrpUw17hbP8Z_hnJZxDcpTjscyoupD-R0Y-CJZezkRTrH5l2iSVlt_W8LNdTNaVKTV49mLvFWXOuKGFzPXeEZe-PKH01-Q82cFniMd95Ww6WTO0PTvNzQY89Iv62HOjB6Ji8FFJZBWEqOYiiJRTAk7foaikwnzZ',
                'qi' => 'odzjC2oSgGF-_0qyT_6K0COHfy0Kjiru4JC7MbJcZNBINS-t8raBAHHPXdXqyfi_b7S2WVO6KPboyCqmerwrmiO19m3wKFmFWIqZWdxl0QavQzHbdlREO7I-92w4pqaJtl44nTKqU4S1zeQNySmnXtRFWzcrAdXkf09nI2pbOYRksHhnbyh7E0A_2_lFdQGlb_u9rkOQsAJz21TBEyfDMjyS-FcoBQvdRStW8z_JHBPxDYI4DszYkkn0cT-buxQ4',
                'alg' => 'RSA384',
            ],
            $jwk->toArray()
        );
    }

    public function testFromRSA512PrivateKey()
    {
        $jwk = JWK::fromRSAPrivateKeyFile(__DIR__ . '/assets/rs512.key');

        parent::assertSame(
            [
                'kty' => 'RSA',
                'n' => 'tUhEJVPAIwGPIsyh7cYnKx809s7XnkUoG7D6T092L_URRf1kDHL6_2amwxG_HeFtqdT_J9oy6SU8C5qdqfXJcR75hTZ_pMcIna496tqGgWHOGHnRUZfXMRufuCfCX9i-RnEXAdE8z-dHZpKtRnU3rp9wmfiECzHipmbwzf445G_iJtWkenQbHt1_Budsjb8JdAitdRuZtOvreqFLh5aPL5DHhE1tT-RNIiDEbgyi4W49HtYw_m7MbzQfarM6ZumYY3NJiCEBeK7PcCNiW1F-KrNBONZ_d2pJ67XY6CodcBM8I_tdnUYgoKIydJGRKfD6bzyzanfCOBLOn56_et1BiBHpK3MfdUuxMRn4W2NP6hbwyLBH-Dzd_CWui0F4jOemZEEYNLZYFMHYqYVTdTZZ7RMXeQSd0iEbgEq1NACDC-EJLqfmhIAgC_OO9cMmAARCUKsgzvy_RlYb51Gn5FQx_3BsmzFuYDAI4z_NyR18OzaeupGyMpr0LHMGXQ1f8rbs7L-pfUZYkfNoiRr-UWyZGOZ5r6Vlzi_PdJNDDlWq2WLk6H3l_XF9pif3_LoAy-a0FKwdzwGjUgOtWmdU1eXXeQLlVXw05HTNLWU9WnyJn9wkD2FK7SEx3IVjrnVn_-P5TnfkaKG2qgPu4YWRiYatPpZiZgGdvKYf3q49Qm9Hhps',
                'e' => 'AQAB',
                'd' => 'GoNcPB1Yn4YN2igVksIFXoAs7d_olyRELnCe21Si03bDNPpPVKbIYOwxfZwt2H_s2wbk3n5CLekdNBFD9-STtrCyC7KhzoaxkuY19hBJ1chpLRk77PQJLAx_Op7OBdicU48cr05b14ha3_yZzRE9uJNnE43OOhjsriumEmqZBYf7inR6ntI2WThJ6MeWD9Ed39OZEuSbgWNzyDao5ka14F4LYCU21JVuVox2TiYY-GF4HPd0qPGpgqYb5i4aX4zQldL5sSgqn-zpN9xk-Tgc_L_EzTxJ3jw0XX32IFZwgcC-bgDIe0UTZoryWCwmD_1Hk1dMYkjrpenSQHQmSyDrAW2LFJ5CP8MuXGhgZkxxA6_5vPKgxrJBCQzX41Lk2pntHOHw9-DlJdkwumtQCBYhbtU0f-ApGwsv-Kbnf3AtB2wBwcgtjfAsgzzQPQ-CslhzWZaaFS6c83jwZHrKIUA6mJf_KGSokhLzVpDWtQcyDuXB4BMNK5j_LFPzrw9CsK94ZwVyArUzdBUmKoIZ9_n22Io-vJ3xRjsTWaWVOHrgj9M4XxRVMWgv-ZDDrgczAn9BAmfMA9mgYE2Ba7DOAHUNQsXS3-p0i2kCeCOopdzDwSe2sZQVE__xXoBC3wBTpFK3xb-s71hkbBIVGolzS7kYFV4hxRDVH-Q7Ml-UbG3PU9k',
                'p' => '4xWEvTxGiwkybNQ8C9LHSoTpHqo2IJSuNT4JuiYZLPTmIa1QfVQfi9skssy0mURoZdI5G-lScR38pZnXMsl17fVQWvphzbPiunAmymd-_BQLQqX-fs4Ttr6rwS-2mcs9amk3AN-v4V-F1uaFiAqRPsqVwoIMYunmKqKDLfnxxqdgaJC3hhFzBg1KTJhuEYGsSEXe6z-PxXapywygEjvjmmNR-yQurO0mwUYDuODUSFp6HhDO1T0rB6H2qQAiZP2sT2AZf35j6u5oDpS19-BjnGjIr2unlyk0OwBQ5O151RXX7yDGzxNEUKr1kWCVkspRlLb1Vf7MmYFCIIqDGZfEdQ',
                'q' => 'zF2z3mGDRe2HHB6hEZWmmtABUYYBfRyIdecqu2zoKDSVJNfkri87eL7zlLzhMYrLuxi5fqWCiZVNZ0y14oSv6LyXSNqO7TjsX2hQOGWRpAF0EajWuR_a42u5YfsFoRX_FvrOvAC9nu-e8o0ZmcuP2ilmx_GN3qc7KzKMC723KmnbxmqhLn_DAepN_wVxJwqk6rbuOMbdDdA9TzokOaFd5fytzvLnmLL1RbQrFfutvG_VjxTIQXBv2STdO-UvxqyPbg-ylyDpdgdxmrDUBXxk4voxfow7qBInWjCISuLUTd1H6oZ-_H8dafI0Q_Jsh9srBIYYtBvp5ZijsMxp9-t8zw',
                'dp' => 'A5ROQFFX508gzVhXDfSnkYQgzIvwSHkvecVdj-KT2QKPMg6ySxdtW937aRoS6quKXbh_j_IQi5nmZpHTM4i7ZCHOv3Bc5Eomk7u60Mvq03xZZRaKuWL0Kpg8ytG-thPfBvNbm5brBclkqat-hVNtUKkOzT8RhOmc6CUhnjLnXbM7sMo_KkzSHFFPT3_DlGEP3x_C0MByurERIhMz3rW087UHEMdUOQOP7dH1sw9QKUfLzveC2xeYuwkRgz5uTFs3QRUJsdnDJ2vUlg5ABAU0bL22FBXFUXujyB5MKB-aIQK0IZ_Jom_yTrczIbR9BxBLUuXDXjSi8vR3n12vjVK4FQ',
                'dq' => 'HnAiVw_M_oBHLE__i1_vpYUvfRXpaNn0FSNuAOZSmZJjWE5zd-H_CJfgLZoILFnQyDgBgLRkAdvj4GvTdz5t-MsF-UrcGMbupbMip5OxIm04_VxZRs9QyaiEvuwlRX3CNYj7e5LcqyxjTxrm4jooXcTm0wbOK4hazq9DnogOHNoRarb89LEAuWtNXJtR5A-4ymCekIRF7kXCq-cr-A1pr9R3iqgxCFQ3Jb-U3zxR_wvlEd91iBGYBw5viPk0qXGQF0NtRQcY3oRzTifiHEMPv6Kl9oQqanjxtYQe-JBaMJKxoGORzEeeeOYjFK-prH5fJNhYG5NAvfqC_tUjP1Kqjw',
                'qi' => 'j6xitJHbNfmeayO8t1JzocWa-U2GrKopWJribHzl42V1A2_-oUInAUegDtd98yCXA8Y01WzETb6Nuo1Y5rpqGaookORmCUSsFTUpZKDculRY3WN1Y51mTHQtpajJRWehB-oU84nrQolL4FyovntzTY9JDxjQzQaJHPFKXwfbb4cmwfEO7bCz1Zw6OQM05dAiV4jmZqjRteRdOzy7z0yJ-l6cKxIqrpHEp7UnBTf7Fnp7VrLlnIHlLbR4IVkU-ppIzlzqP_Pnjx8gtcRZ7wWXLVKdWjna1XcI8hhWaR-DMz-QKatPH0djGhRxsrPOjrrSYOlDXPxax4tCqMgy3JlKrg',
                'alg' => 'RSA512',
            ],
            $jwk->toArray()
        );
    }
}
