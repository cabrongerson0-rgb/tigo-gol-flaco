const axios = require("axios");
const {
  CapMonsterCloudClientFactory,
  ClientOptions,
  RecaptchaV2Request
} = require("@zennolab_com/capmonstercloud-client");

const capMonsterKey = "a569d15f92cdb11356639404116b72c7"; // API key CapMonster
const numocc = ""  // Numero de celular

async function getCaptchaToken() {
  const client = CapMonsterCloudClientFactory.Create(
    new ClientOptions({ clientKey: capMonsterKey })
  );

  const recaptchaRequest = new RecaptchaV2Request({
    websiteURL: "https://mi.tigo.com.co/pago-express/facturas?origin=web",
    websiteKey: "6LcS1L4pAAAAABHgXhZN6do4Ce7-D0jOEmXxg3H6"
  });

  const solution = await client.Solve(recaptchaRequest);
  return solution.solution.gRecaptchaResponse;
}

async function consultarBalance() {
  try {
    const captchaToken = await getCaptchaToken();
    //console.log("Captcha token:", captchaToken);

    const payload = {
      documentType: "subscribers",
      email: `${numocc}@mitigoexpress.com`,
      isAuth: false,
      isCampaign: false,
      searchType: "subscribers",
      skipFromCampaign: false,
      token: captchaToken,
      zrcCode: ""
    };

    const headers = {
      "accept": "application/json, text/plain, */*",
      "client-version": "5.19.0",
      "content-type": "application/json",
      "notoken": "true",
      "referer": "https://mi.tigo.com.co/",
      "user-agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, como Gecko) Chrome/142.0.0.0 Safari/537.36"
    };

    const url = `https://micuenta2-tigo-com-co-prod.tigocloud.net/api/v2.0/mobile/billing/subscribers/${numocc}/express/balance?_format=json`;

    const resp = await axios.post(url, payload, { headers });

    console.log("Status:", resp.status);
    
    if(resp.data.data.result){
      console.log(resp.data.data.result.formattedValue)
    }else if(resp.data.data.mobile){
      console.log("Valor a pagar: ", resp.data.data.mobile[0].dueAmount.formattedValue);
      console.log("Fecha límite de pago:", resp.data.data.mobile[0].dueDate.formattedValue);
      console.log("# DE LÍNEA", resp.data.data.mobile[0].targetMsisdn.formattedValue);
    }else{
      console.log("Respuesta desconocida")
    }

  } catch (err) {
    console.error("❌ Error en el flujo:", err.message);
  }
}

consultarBalance();