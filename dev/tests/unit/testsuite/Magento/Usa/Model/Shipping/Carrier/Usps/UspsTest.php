<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dkvashnin
 * Date: 12/17/13
 * Time: 2:19 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Magento\Usa\Model\Shipping\Carrier;

class UspsTest extends \PHPUnit_Framework_TestCase {

    public $_model = null;

    public $_helper = null;

    const SUCCESS_USPS_RESPONSE_RATES = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<RateV4Response><Package ID=\"0\"><ZipOrigination>90034</ZipOrigination><ZipDestination>90032</ZipDestination>
<Pounds>5</Pounds><Ounces>0</Ounces><Size>REGULAR</Size><Machinable>TRUE</Machinable>
<Zone>1</Zone><Postage CLASSID=\"3\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt;</MailService>
<Rate>24.85</Rate></Postage><Postage CLASSID=\"2\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Hold For Pickup</MailService>
<Rate>24.85</Rate></Postage><Postage CLASSID=\"23\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Sunday/Holiday Delivery</MailService>
<Rate>37.35</Rate></Postage><Postage CLASSID=\"55\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Flat Rate Boxes</MailService>
<Rate>39.95</Rate></Postage><Postage CLASSID=\"56\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Flat Rate Boxes Hold For Pickup</MailService>
<Rate>39.95</Rate></Postage><Postage CLASSID=\"57\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Sunday/Holiday Delivery Flat Rate Boxes</MailService>
<Rate>52.45</Rate></Postage><Postage CLASSID=\"13\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Flat Rate Envelope</MailService>
<Rate>19.95</Rate></Postage><Postage CLASSID=\"27\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Flat Rate Envelope Hold For Pickup</MailService>
<Rate>19.95</Rate></Postage><Postage CLASSID=\"25\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Sunday/Holiday Delivery Flat Rate Envelope</MailService>
<Rate>32.45</Rate></Postage><Postage CLASSID=\"30\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Legal Flat Rate Envelope</MailService>
<Rate>19.95</Rate></Postage><Postage CLASSID=\"31\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Legal Flat Rate Envelope Hold For Pickup</MailService><Rate>19.95</Rate></Postage><Postage CLASSID=\"32\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Sunday/Holiday Delivery Legal Flat Rate Envelope</MailService><Rate>32.45</Rate></Postage><Postage CLASSID=\"62\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Padded Flat Rate Envelope</MailService><Rate>19.95</Rate></Postage><Postage CLASSID=\"63\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Padded Flat Rate Envelope Hold For Pickup</MailService><Rate>19.95</Rate></Postage><Postage CLASSID=\"64\"><MailService>Priority Mail Express 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Sunday/Holiday Delivery Padded Flat Rate Envelope</MailService><Rate>32.45</Rate></Postage><Postage CLASSID=\"1\"><MailService>Priority Mail 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt;</MailService><Rate>8.85</Rate></Postage><Postage CLASSID=\"22\"><MailService>Priority Mail 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Large Flat Rate Box</MailService><Rate>16.85</Rate></Postage><Postage CLASSID=\"17\"><MailService>Priority Mail 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Medium Flat Rate Box</MailService><Rate>12.35</Rate></Postage><Postage CLASSID=\"28\"><MailService>Priority Mail 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Small Flat Rate Box</MailService><Rate>5.80</Rate></Postage><Postage CLASSID=\"16\"><MailService>Priority Mail 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Flat Rate Envelope</MailService><Rate>5.60</Rate></Postage><Postage CLASSID=\"44\"><MailService>Priority Mail 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Legal Flat Rate Envelope</MailService><Rate>5.75</Rate></Postage><Postage CLASSID=\"29\"><MailService>Priority Mail 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Padded Flat Rate Envelope</MailService><Rate>5.95</Rate></Postage><Postage CLASSID=\"38\"><MailService>Priority Mail 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Gift Card Flat Rate Envelope</MailService><Rate>5.60</Rate></Postage><Postage CLASSID=\"42\"><MailService>Priority Mail 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Small Flat Rate Envelope</MailService><Rate>5.60</Rate></Postage><Postage CLASSID=\"40\"><MailService>Priority Mail 1-Day&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt; Window Flat Rate Envelope</MailService><Rate>5.60</Rate></Postage><Postage CLASSID=\"4\"><MailService>Standard Post&amp;lt;sup&amp;gt;&amp;#174;&amp;lt;/sup&amp;gt;</MailService><Rate>8.85</Rate></Postage><Postage CLASSID=\"6\"><MailService>Media Mail&amp;lt;sup&amp;gt;&amp;#174;&amp;lt;/sup&amp;gt;</MailService><Rate>4.33</Rate></Postage><Postage CLASSID=\"7\"><MailService>Library Mail</MailService><Rate>4.12</Rate></Postage></Package></RateV4Response>";

    const SUCCESS_USPS_RESPONSE_RMA = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
    <SigConfirmCertifyV3.0Response><SignatureConfirmationNumber>420945389449301699320000105074</SignatureConfirmationNumber><SignatureConfirmationLabel>JVBERi0xLjINCjUgMCBvYmoNCjw8DQovVHlwZSAvWE9iamVjdA0KL1N1YnR5cGUgL0ltYWdlDQovTmFtZSAvU25vd2JvdW5kMA0KL1dpZHRoIDE3MDANCi9IZWlnaHQgMjIwMA0KL0JpdHNQZXJDb21wb25lbnQgMQ0KL0NvbG9yU3BhY2UgL0RldmljZUdyYXkNCi9GaWx0ZXIgL0NDSVRURmF4RGVjb2RlDQovRGVjb2RlUGFybXMgPDwNCi9LIC0xDQovQ29sdW1ucyAxNzAwIC9Sb3dzIDIyMDANCi9FbmRPZkJsb2NrIGZhbHNlDQovRW5kT2ZMaW5lIGZhbHNlDQovRW5jb2RlZEJ5dGVBbGlnbiBmYWxzZQ0KPj4NCi9MZW5ndGggNiAwIFINCj4+DQpzdHJlYW0NCv/////////////////+WdzloMwSEHQcyIA7Acf/L2UkMG2pDFZD8QQ3qPsP/hBv/9Bv/6Tf/8N//p//7f/9v////2//7//u//7//b//7f/u//2+TYayI//d5bpYY//23luLhj//3+Rl//2+cCX/+tcECKAwfDH/pusEC//7a4IEQPIj/9fwX//bS4P//74MH//22hBg//+24MGI//2i3BRg3/9tpMnDH/8UbHnwwfDH/3QQbH//trc+HvPh7/++nP////7+D///+/hh///+/wYP///32sH///93qeA////eQtMMJYP///7wTYrBcfH/94TaiEIiP/tQmMF/7wmCBFPkR/+1BMIJTbU2//IYHaQTLfOCSTST/+w1CSm2KU70p3//YaQVJOkm6Sb/+xgiDD0p3////aCSVJNnwx///9oIL/////hoIJL8dL0v/7QIL/b0m9L/4YQQSWlxSxS//DCCC29IzDDVNV/8GEECWKXERH/4ggqaoiIl8iP/4QSiKX//CCoIL//hBaX//BAlCC//4SpI6hg+GP/hNQgv/+E6SI1kR//BNS3PohgR/+digZAN8Ihoh1B///wgm1DDH//6CbwYP//4YJtKDEf/7Cb54D//sJuiJeD/8dhNqngv/2E3ScQv/i2qcF/zsUDIBvt6YIF//bSTCC//sacEQmS4zqP/jtJiptpp//dNJNGxo2P/7pFcUDCU70EG0EG//tL0k2trf/3SK4Vf09P/9pEKPS///+digZAN90kggv///+klpaX///9JIIJvStbX/+qWlFK9PX/+kkggmqYYSYYS//qlpCLFMV/JaBj+kkkQwIOhNf//S/Ef/+qSQ//x6S//L4n31SS///0lluahj//6pJFcLP///SWEGQn//9UkkbH//87FAyAb+loIN///+kku///+qWk////pJLx///ql5v///4aSS////ul/mtf//3SSIvXpX///aSwm+rf//90kgnGl/xEf6Swm4mgY//ikkEx/+OlhMf/pJBP+S0DHpYT/+digZAN9JIJFccMf/yUBs0sEv/+F2kgiGB1Ph7/xxXawgkv/+u0kEEl//12sIJL//nYoGQDeSBbSQQSX/8hYX0FkmBccIJIpF////SBBIOD//hB/X6CTOnB////yTDKCCRDjtNhlWCjH/hEnJuH/jhAkoIjtMGDxH///0FS20wYP/wv/zsHGxQiDjhpbDThzV////pBBBtLDaczg/O1UMf/8byTAv4VtLYvBg///+3+kEQcexW3h/n2Nf/8hlE/xCw1t4P//DDiFnYoGQDd/0Io2Gngv///8eQIdXhf/4eF4fiFUgYHeCBf//xD8zhFEfDkiNh24gv/4jj7C0tJuE2vlIDX/yTD7C0tJiC6HIQNX+digZAN/FoUhSYKGFwv/90kmmGEsL8hYXyUfiddI1aTbFHawGNf/hTQ8VSQtsML1+EH6YQeqpthhT4e///VPSTRDDtsf9fhEvDxW87IBRSIc fTv///4S5JgX0km0m2y7/0Q6n8IP1JR/0v0If/C/+digZAN0kr8isfor9P/r//W//WvM5Ej/pf/+vH/RDj/bb//8ML//9qtqk74///9f9OkLrvEevh4X9euhoe/////JMOuyCOkQzjrvMlf/xH7VfXaYSZHf6m3//9Lwu0Ke+kn/8W0gvomO1ZYwh3eqRIBQlO//lKfHWISbSaSbbj6Sb//teEFHZySCOO7S/+/kFDZ9dNLIjoKw2KIN///w1C4QYXFbBtN/7fwg9hDkmHhCkzjpJ2m9L7/4+KVmw6TkYI23pcP8tBZV+nSp1Til5BVr//+0ttIJtVv/87FAyAb48V2pq0TdiO///64YSTSb/4j5Egy87IBR1wwqaTe///65hxIN+aCb3/kkL8kw+2jDptJ0Kb3/ksgg/+aZuw1ahq6TeH+S0M+FTH/9inYpkDFKCfJMNL/qmVxZmj/9p2q0nx/+v4/hhQQYRDAjSc7WR//WSx//iIsKKEIP+Vp8KE//4MKkXj//X//netUIP/DD/yKBkA3/JMC//S3//df//JPI36VPknf9h2l//93pfgv/iMbX/2220vwp1F/7Ta44/6X6hB/9qK8W24aVrphB/9rxbbYaTaWqfxwYJrOwUG5IBuxTaWqf8Wskw92Gg9a/yFhc7FAyAbhhf4aEfT/5LTx8RBrhL8IPv8yISRko9BX/4Y4kXBRBpQfUhL/CJeH2Vyz+KDD6Sf/8MKaH7QMHpbf4QfljtMIPOwMwag/r//hhqnkmHBangPpX//trflSCFOyBtPB///+GHCXwQK7QPBfr/DC+3UlHyVhLtEO4OIX//8lAGEkrxBAoIjuCI7SDwXr/DELyKBkA3rfRGN29tIHBAvr//9fMK2HsNIFCC//hhf/8jG4bw2oWC/X/46+SYF2Qhi3tqCBELx7VL45LX1+DB7D2GhBJ/Szrz///ImEw3htEOOEnt4X/5VAyAb2q+DBkMG5IBuhBJ+oX/j+liSoW7QJPhpV/yWntpBcH9BJ7H/yWhn746zsgFBqiNSBJ/X/4e1/6BJ7Vf/v6yTD/pJnYlmD+v/w8NQvERaTVB7VfytPljvYQ+2ck0ka+1C//hh4+2FPtEOO6pvagv4YftjixhBpN/4YQ//ww87EDYSaTf+GF//bwQNhTRSb38f/koAwRQMgG+m2k2k2w7S///kmBdE/be6TbEV///0EG2/SbDX/JaGf/0m2/SbH///07aWk3/4WS1+PkQKSYrHpNnYMT//jpNggwQfpNhB/8f/TT0km56POoKv/H9E/aJ+3VJtJ//5bjfkmH9BBtBBuwkk3//8khBTQ/9JtJtulb//5LQzl3ksgg0wg/+np2KTb///4VNU/9bW2Em3aX//1TW8W0qenIwIm2x///1hL//tpthhf//WSxkUDIBvUlHtf9tEOO2x/+I8KE+kle2l/tpNt5GxSQL/6/rfJMC7DCX+0k22dnE///49fsV/tJNtBB///df/atK0mkm2p6P/+QsLsO0tfwYJ1dWkm2kn///EfXiNpWlSTbX//wg9r/YasNRSba///7Vqu3CbhJNj//8Il4e16W9PSTdpf//wYJtILkmHIuCgOIcUm2P//CD4x1+IpNhhf//zsUDIBu1/pNj///+vKwaTfa//8NQsUkmzsVz///hhfYQ4QR1BQUAoabW9tf/+OEF7baTfDCX8PC/S9tun4//45JhwgiNRGptvOf/8R/CC9tv/a//pe232u//LcmGX4QXtsJtBAhXX/8Qgvbaja2v+S39L22g0OGv+Eyj9EFAPttDsL/qn/7Y4ML/rDHJMP+2dmmaeP/8ioZAN/H27v/6Cv/27b//Qf87mCgoBQ3CI+//6M24ksFxK3e3t///wQf+3/knf/tQg/EW9vwX/w0MigZAN0/b2GuFOov8loGNitPJMC+3sVqEH/+QQfoi+/28NaYQf/2muED/tiOqf/2mFoJv9vVP+SUzzw0PviSYKCgFBQChvX//j1f+36f//X/2+Ev//p87G4l4/EvH5vQV//yChf8kwL/+31IS//8ED///2+kn/hfCD8+///a0tv/+mP//+19f8L//jrrtdK//9Eg3evrrtf/8gophhenIoGQDf/116+v//0+3pYhcLj/////wuSYdEx2qJjtfX/hBRC7UY0vCTaQSbS+v//av+EFGEFGdhS//8tAWxXX01TSf6//8ioZAN3axhBhQgwk+1S/wh2vhcIYQp/S//DCxHEU9vC/xwYL0/UL/x8kwLt8NKv5BSOP/b2P//5Egy/ImK39fwg4f/ggzUCgoBQ3tV//JK/xT9v6/y3h9sfRP37e1X//yuqloINiLe1C/4YP2wuk23tQX//si4ZANwlkmHp28MIf/++EF623hhf/8lAF8IL6c1RGpsqfj/DEfYhBf4U+wp9un//wgsfUINQg2k/+EF4QX6CTQSbSf//CC/hTRhTRaT/863EIL/Sb0m2k//4QWSYe0kt0txT//oL3X/9P/iQ3wvtL/9P//IZZPsNdL0tP/kFDZnYoGQDfxbhf/p///3r/9EOO/+EHkFC/hx1S1SSb//ggYji3St0qTf/loLKqeSYFyTVqErUJJN//9P4NiqYqkm//+mRUMgG5El8G1TVJN//+Twu/B+w2haFJv/iOk4b4PEGDEUm//1fw8GDSf///B8NJv//rsPmcGROI1JN/8hYXsKFwweSYfBg/Sb//sdYMH+H9Jv/hB9fD/B/SfOoKv/2q5bg/wX0m//hEnJuHtQvBh44X0v//+GEPh/BAvSb//C/EcH4gvSf///g/CIwFBQChL///4LkmHBAvSv//+digZAN8F8EC9J////hfQRCojUl//DDiF+CC+C9LyphP//EFjBel5BQf+HhfCx9LyVBn//4IF+l5FgUfxHEIL9LyNhmf9Askw/peUgNf/gv9LyEDV/5JCJSGQDeF4ikuU4av/JZBB8F52BoiSS8EQ1Cf+FT4LFVSrkGoD/1TEcKFSXkMsU/9SupvSSS+QUFH+slj8JBJpLkM5Z/woT+SYFyB54kkm15Bwv/r/wqDCCCCb+Qzln//+qaSTaXIKCj8lv7r/SLxhIJNryGWKf+w7S8UknJAORAO2lyDUB/yKq4j+qbhQm0uQ1Cf/tEWDIBuRIMv/hw21wZDVB/hkEBLX/5LkTBNpZCBq//a/JMC/4bDbaWUgNf8qYYwYL//YbDbSyNhmf/H/bfDYbbUiAxkWBR//Ky/u0mGw20gQPJUGf/LwhBT+LDbSDYbbSCDyCg//8khfsRu7DCQQeVMJ/yh5LIIP9hBw7YSCedQVf/4VMfBhO7DCSD//nAmqfkmHERYMEgn///X9iEiGe3/+JIorJYyKBkA3/JY2kQXDf//hQnx+2kCDf/yCgnX+NsJIN////4IjsMJBBv/4RFVe6/thhJN//9h2lkoDZ7DDCQTeP+dbxGIXJMPhsGQgNP//aX9sUm/+O0v7Dp//tEoDIBuv4bKeI1JX/yLgYwYLkgWJFwbhT7Cn2k//8dBdqEGoQdN///9BJoJNL//9Z3XQpowpo151BV/kFZ95Lf8kw/pN6Tat///CZRj/pbpb1//CCDD1T/H/////rD///S///+RUMgG/iSvOWl6Xv//9BX3hZE0//V///oPt/B//r//hhejNvyGUTpM4/VLVKv///++SYF0rOTdK3S/yKgin/wxC+1/6ftQlahLX///DQxD/3sVTFV///DC2KD/+1TVdf//5BB44/tC0P//8dpr/Edf//tML////4aHbS///+OSYeGuRqI1aX//IuBj9iv////luTDL9r/X//+SjK2GQDf8MIfbS///wZIvxH///5BWffcP/9L///w7HzurKP21//4QQYfdlcXZm9b/S///9/5JgXWyGCgoBQ3X///sG/9Jv7X///yY7f/p/dL//wwvt/+k2RqI1WEv///t5FAyAbr45nftr///9hrr//tpf/8MQvY9f/+Gl4//9rX//bCX/8ML318kwL9r7DCXj//awiMdr7adewwlj/HwwtK1+0P2QYtf/EcJRXsTOhFiFn//6T4tMJrT/41WGhDC0/5LQz8JrFrT//QYWZVAoDC0//nYoGQDeEOSYclZ4MLT//16DNnhgtSOCr/K0/H00ytR+K6tx///0XdPx/+GH+NMT3+v/+RIMvSab9f/4//6//yChf/XX/+CB+SYF/11//hB//rpf8dOXQvv/hdZFAv///bTSRMdqiHH//IWF0SDZFAyAb/42gRHECTaSS//+n/9jaCCjS///T/+0wqaSr/4Qf4/7CGEGEv//7X/JMOMIV//hEvD7X/xQIj6///YoioZAN//IJJf/+EH///S6//+1/46X///DC//S///4MF/+VsFCX6/4YXHx/0tb///jkmH9Lr/4YheP80zdS9yHpf/IKF//0vtNf4YWCBj4/pfaa//CDx/SjYaaX8dPO6so/0qg0wX/5FQyAb63+lsRX/RIN+t/pf/6fyTDpN/pbX8rRdP/T/pbX///Sb/SoML+VME7X/M7/S2v/2vj/jpYYX8loJsV/+KXC//5WX/ilohoEv/a/7VKkQpZ/JEsML8kwLtp01pf/Bgv+0NrS/yCBUR/YmdNaX//2mE0qX8qYJ+IaEqQKGqS/+QUKSsMgG/j1giCrX+VosEDyJBl+P//CD/OyM680zd/+On+SYeHD/Vf//93/S/9Eg3/2H/1/9OJWX8ER6/9A/5CwvT+O2/+g///+/+qD/hB9r/f/SIWt//tf2//SQf8Ik5Nw9ivJMPt/+kE////7a/6SIZ7f+F+1/sNLHVUQXDf//DCH9jxpBEdoEG//+DBErDIBvjtDFQ0g3//x/DCqDQQb///iNQ0mP4YcQvIkGXmQrJRCf/5BQv5JgX6p/h4WCB/5A88XVP/8IMfwqD63+I6f6p9Qd/8ri7M3jSLx6QYb/0SDZFAyAb/pJPtAw2/9P/1Te0wf8hYXp//8ggKGof///yTD/7SBt/CD7XX//ah//tdf/zseeD/hEvD2KFf23/RB4D//6+N2l+GG/4QfaX2G2l+D3/8MIlAZANwiMdrsR+oP//BgtK12F+H//jhKKyTDgwvwf+GGEF6T+P4Kv/+q8fC/whIcU8JryWPwWv+NBhY34L/+EPfhV/9cER3xCzUv+digZAN/2+Fp/H4PJMPsPgkk//B/hvhaf/h/t8Eqf/g/2HsEkn/2HjhvCC2/5JWIYPOyAb4QJJv+2DB98Erf/h/wlb/ty3B5JgXI1cFt/7wYf/CVv++H/9AiPt/yUAXIqGQDfB/HsJbfyps4+/B8eRjtdv//4L8E2lt/Ja//wX4KGlt///wvi2Etv8Qfj4ILJMC+wwlt//GIL9hhLb+QsGOF+yDFrb/4IF+xC0/ktZdmA+EFjtaf/9AvDC0/xBl2XfBe1p//yChSVhkA3heGFp//wQPgskw+DC0/kLBjCD4L8MFoh3f/Tx/IK9Jv4ZcEOKf/HSb/+iQbyViY+k3/DLf04ggf6Tf/+nT/Sb/4/hPLccFf0m//2pFAyAb3kmBf9Jv4j2uiOj+cs+k3/YrQQb//Sf/9P/+k1/a0nx/OoKuk1/DC6b//Sa/gwQ//+k1/H//0h//kmH/9L///+l/Jb3//6X8Jm/X//S/qiMrIZANxNpY/+l/WQ4thr/lTCaX/h7DCX+QUH0v6C+vynH5KgzxX+n3rJMPggfkWBRX+mZ+Ha/T8jYZlf+w4hqF+n5SA1pf78R/RL35CBqpf7C+PT+U4aqXyqlsf9P4IhqA18hYP1+k38g1K19B7X//IZYEvp4ayKBkA3IkGXkmBf/5BQNr6Lsu3hhD///IZxRfp3x///kHKEd6f1t//7/kM4qk//f+O6/IKBKn//IKFyVl8NL5DLA6f/8EDFv21+QaiYIj6f20ksINv+vkNQItP8YXTbyTAvhprwZDVB0/tNe3+HheQgaun9qtEg2g/xHykBr0/hhQunT/8jYZml8R6cigZANwnjmoJ5FgUa//wn4QPyVBn0v9roiQ/T8goPohR/ypIzfaiEE36flTCaSXwoQexQSDeSYfRKj86gq6X9U/QT/wg/+kl9NPaQT///pf4Uu+GFT///pJfSSbwYKE+P/+l//xhP8ML/pJf/6H2K/0v7/j7X+kl+3rOxQMgG+SYfa/0v92v/gwXjpJfYiv/H9L+1iRIMv/9JL4a/HNYnnVaX8khf4IH8HpJfH/T+Hpf/9P4PSS/jkmBflwXH4el/KkjN5SjIoGQDf9Jtv4PSS+FCD//9/h6X9U////g9JL6aeSg/x//h6X+FLvwfkSDL+1X4PSSIvfSSb4P/2KS8MPSwn//h4//4MPSSCf/+H+SYfarwYPSwn9/yWh8rhRfhhQvDD0kgn+3rIWJhfxH4PSwn+7XBjCX/w9JIJ/YisOEFjmoJ8HpYT+1hwgvCB/IsB6SQS+GsOEF6f4Yelgl8khbhBen+D7SQRDA6+O5FAyAbhBZJgX0So/wfawgkv44QX8IP+H2kggkv+EF//4PtYQSXypIoWS0xCC//8F2kggkvhYfaCx//C44QSX1vhwvhhfwXSCCS+mn2QUMgG8hlk+xX4L4QSX4Qfh/7X4XSCCS/R9N8sd/7XxC8IEl9Yf4Yf+DBfC0ggkv/+3j4/BeEEl//ww//haQQSX3tL2//CBeEEl+0PJQBj/wQLSCCS/E46//wQXEJL7v4ksF/wgWgkvtBheCD/4LhJfDCHwg/+FoJL45LWVQMgG6f/BYYSX/0/zslzdwWwkiWr/6Ivv9U+FsJIE/ktRqWIwgf9JHd8FsJIJ/g4egm/634Wwkgn+0/f//gsUgn+HfV8el8FpAn+w+v/+OkE/2/T43r6QS/LH3/sUl6QX+3/7VekCIMP/t/n34jnUFXSCSX9v//9IIL/sNL/ybioJ+kEEl/Y+9f/SBBf9rOxQMgG/+Tcs/0ggkv7Xt6W3+kEF/wYX+F/9IIEvxH40v/aBBV/38m4qE/aCCX/9dY7QQVfKqPF2sfaCC/kLDHwv2gQJfhB4jk3FQhH/oJV9P/8Qmv5NHybk8R8J1+EH8m5cfgmv1vOxQMgG+TesQ8D+EQ0Q6//4RJHwgm1//84g9rOoKugm/71jyPRN/hgm0vwwvhkQ//YTf+Pybgytf2E3RFP7WdigZAN8m5PYr9hNqn8ML8m4Mmv2E3Sfyqj8lLOPJvWIP4tqn8fIl/hEiz7en/4/nCvtpJ//+SLMiktjS/jHhkQX+0l/D5Ny4/3S/luNhnybk/90v7DxMwT9pL/Lvk3rL/dL+W5P3056f2kv8cu+jDgiPf+6X/x63/9JL/H///pfw/6/+qREf8txsM+3FWv9JJ/2HvCbS/VJP+digZAN5d8RYr+kk//vhr/ST//Lvk31CEfH9Uk/4+P/9JJfxHETRl1+qSX/Tv+kkv+TcjMFO9v9Ukv+qf/9JJf+ka3t/6pJfzsUDIBv9O3+PpJf//Qj+kkv/22lXqkl/HYix+kkv+5Gfqkl/xKATw0kl/OxQMgG/+6SX/yb6gk/AvdJL/+I+0kl/8m4P90kv/tkMvpJL/+n4pJfx9GHBEe9JL/k31CVv6SX/r/0kv+Pr6SX/JuE24r0kv+TfUEeF7SX/xH2kv/PwL2kv+I+0l/OxQMgG+TcVz37SX//+kv/k31z2I+KX//9L/4j6X//S//6X//S//6X8f0v+Tct/S/5N68u2/S/nYoGQDfWDb9r/6CZTzME9v//b/t/xybKYT8fb/4QP/2/+mxX7f87FAyAb6bQ+3/9EqMTpn/2//wg937f//tv2///CI+/t///b/b/jwwu2vt/9itivb/7R1BNr2/+1iPt/8GCEz/b/4wft/8m+oIGD9v/k2UwmDB+3/wgbPZxAft/9NJsH9v/p+UYQXt/9EqO1u/a/8IOxVt+1//ahEff2v/47f7X//bX6/8MLsV4/9itr/+0I//2v/4MESvPL/+JLiLf/9NBN//ybKYRG95v//4QOn///p/xX/9O1YlDr/+iVGxTQ//4Qdof//iT///4P//4YP//DCgwf/9igYf/9oH//tD//BgiXIl//+Kbf/9Gtt//ybAmSKnKAT//Dh///7u1H//exX/+w21//tx//yx3kOOZDzy//7fet//9vvQTf//bWCI95v//+xW3///99j4r//tbsSh1//w1u0P/8RER///////////////////////////xERH/4///////////////////////+dj8IIuiBHSZStasILuU4m1CCthBhBWIZ4SdzggrEKeBgzDQGSOQfCDzILzILzILzILzILzIL0IPMgvMgvMgvMgvMgvMgvMgvMhiZBJkHmQsyFmQsyEmQ8yCTIJMg8yHmQeZDzIWZB5kLMh5kPMizISZCzIWZCTIWZDzIWZCTISZCzIeZBJkLMh5kPMhJkEmQsyCTIeZCzISZBJkPMh5kPMhJkPMh5kHmQSZDEyDzIYmQYmQSZDEyDzIcl+cdf////////////////////////xEiBBEREREREREREREREREREREREREREREREREREREREREREREREREYIjwyarCJ+DhGdSHTRwgshiYSQMP4MiJp2VCDDLQtjKtEyyIR9EDyRm+0DYQa6r/EG0XjVVVcYoJ6qqpKk3//Sc3n8IM6Efy5mp54ggwgzxGj83m8uZmWh/CemE8J+Ewn/phPrhhIu3Eq6NY0XbDCDCRdtGsesSrou3SpLhNpdNQm/hN00lSXCb/pJPWk6TpJJOk/1pP//r////r//pfpaX6Gl/6H0l/1///11+Q/pL0q0vSSnDpJUlU7NvS/0v/S///7fr///+af/+ae0te0vtbS+1tf+1thLXbC6thWwvthJsLrq2ErFRKHUNikqimKhw2KikqSpijnDCa9heGW9hewgy3/7C4iIiIiIiIiIiIiP///OwQySo79GZ8KF+q/Vf+P//zzMEQxARD2W5OyfkzkXiHsmgjQXBIMiNoX9BhB+n4QfhB6DCD8WnxXp+EHFp//9P0/8jtoljBk5/RLIMnPJZkdtEsfrphcjn6b9JuumEWjUUixnjOxoyUGSgzWDB2Ln6ex69WF9PT2IIH/8P/wfX0PpN/Bp//f/ZD0///7IdoIf/3/4f///w0R88f5M//hv///w2gg3/7f/Bv///wbSb/9/+dR/+aX/51HTnmYLPGeZgjoMqx5Bi5giGP80GdBToMoZcz5f89B/+4ftpeeg/6D/QYQYQeg8IMIP8IMIMIPTQf/Cf+rr9r4T1bi/i009OLT/TTCDigh/21zC7WGlmF2l7a1/6aenp+qaaf/4qPY2OPY/ivkdv5HbRLGiWPRPMjxolj9JEsaJZks6JZ/tfte17X1/XT0/vT0/HT02k3I/T/2n2Qg9p8ML2vp/p0m0m8NfaTfSTasKnpf+GE1hhYYJrDC8ML////Hp/4X0KTdU8RERERERERH//448L8fBR/////8wFyDD/8nj//9r///gv/t//96//k0hOvoiP+TT9uTq//////k+/+35pf/tf/7sdLtb9u7htpdrbS//cznM716czvdzOdbXfsMJf/w0HDQfaw1hoP7hoOGlaXa2K//YpipDPA4pivbYpjY9jtf/tNbIUfTXu001va//aa2uWOmvtpoMLZCD4MF/+GEGFsK8MIML2wwgwQYWGFxEREREREREREREREf////lpFgEoNP/+dq8hA5+MicdlEdrEbilUmylZey3WH/5zyX9et/+uun1mn/xx9Phdv//T6LH7//7fhf58icjQicjrHo8RORTlnyNCJyPxHZDI/Ed58jQicj8R2dWXjcej5ZuCDI8fieLs/Edl43FzCDPEfiO+/X+gwgwg0H4QaDCDwgwg0HGEHHhBhBoOMJ/hB//oOP09Bx78fXRraNjRdtGt+i7aNbRsejY0XbRrcvQu8vSjY0XbRrcuouB+jY/DCbSiVDl9iVDDCRrctcEt/0tJ0np0n+nSdJ9J6dJ+m/SenSfp/Sf/6f6fSf2/x6Tf4pN/ik3/+KTdJN1/ik3ST///03XXpN1t/r8eH/w/Hx4f/+PD//x/20v///b/C4wrBx+wcYXCsHH/4Vg488L/hf///4/v81XNhQwf4YPJAXOBQwf/5wKGD//zgX////9//NoRvBuYHwbmBkb9Ebwbm0/6I3g3Np/9Eb/20v/+bTllJpv/6L6Df4N6L7ovoN//ovoN+P+i+/////hNv/2fqzE9n7zE9n6vrMT2fv/rMT2fv/r////s/em3/3S1vdLW90tfW90ttL9b3S2111/Yq0v/dL02/md9ratra2ra2tr2ra2va/atra9ra2v/a//a5nad//YpiUPYpimJQ9imKYlD9iUPYpitivYlD2KYrYlD2JQ9iUP+HwiOP/hsV+r//DCabYQYTWwgwmm9pthBhYYXtNsIMLDW1tN+7Wwv9wwv22+IiIiIiIiIiIiIiIiIiIjiIiIj4iIiIiOUCmjJmjvCKmRHZ2ao8ySilYZCiM2wbf/wg9Qg11wg/3bJSvpenqEGuun3u2n//FU5e+jj92n8JeifPk8+ifO93T/X0+kG/p/bbT4iPicZ8zDNswZOMIGYZ9m2RzPHWZkZswYQMuzbI5l2XI2ZOMIGYZmRmzB4mhmbI5kdn2YZmRQcsd5tn2bZcjZsguggwOTgvV/g9B+EGEH/FBBpMIIPCD+K0wgwg/CDwg4MIOK/CDCD//TCDbCFYS+w9Djv/TxTj/iO/TjYaf+mnt/xG2ul+D0TvJW0XcMF8n5O3onbkrYYLk/JQRw0XcMFonbkrcGiduT9paJ3RO3/8lBHDtE7IJOl9h9PTtP/wn6en/pp2n+np2HCf/puE+3/TTshR3CcUvhvSesf+lvSbr+qrH9JusNrdPWlCW//qtsjtdBfBv/wv/H///4X/8G4//GPb//jiwl8G//PhP/yj///z4T/8G/tf///7S+H/8F/+4f//+C//h/ev9v//tL55v/8lH/lhK///8lH/8kN5em/5YT//8shpfv/9Av/7S///0C//7/0vP/sf//aC+2u2v/67batpf///tpe2re/tw2/1/23CX4rtfte17N0Va///a/a/FWbuPtKzdva/3ZubLgv99j+ODY9iG0xX//xwexXuxD2tjYh+x/2xDuvtbIo/2RB7yIP2mmv//ZEHvte01+01v/yx21FfDC2F+GFbhheGEDCDC//8MK3wwvDCDC/DCDC8P/thhQviI4iIiIiIiIiIiIiIiIiIiIiIiDQiIg2INfIr6Ddr8NJBttfY0G21+kG218MJhtsL4yMcO652XMgRlPG470iHEViWxERAo7SR2oiRGX22RwXdrhB///kP855B/4YbbXTQIj3//r66/bDba81v/xH/x/9w23C6Qf////+ww2x/pN////+GHfH8nflzNCPxHZwjWWXM9G4uz8XZ+NxczRl7Pll2TlhBniOsXZOROWcZwidl4+RDj0ei8EHlzCDPEfi7JyKcj8R2Q2Xs3Ho+W2w3/4T4tBxoMIOL9B6D9MJ+EHENB+EHoMIMJ6QQYT0H/8XhB6DQYQcYT/0H4YbfpJtFwOTho1uX5raLtycPiVDiVD4lQ0XA9Gxy7aNbwwkXeJUNGxou3pI2NFwNGt6+GFJwwwkbHEqGjW0XeXoXA/0a32GG31+np0n0np6fp6fp6fSfdJ/punSer0knp0neER+vp9J6dJ6b6f9J9ww2+l6T1pN0k3jX03TfWk/9NpN+k3TfpPpek6Tf/9f9NpNpN0k/6TfsNvpJN///w////8f///Hxih/+1df/H/////IQioiXjqRAxlORCZrGQMinI1jNYyNEU5FORTkRXNZMg9AzWBBT3Sj/4+Ng////8L8f/4Xwvx9X/+F+P//48INVCDCDCDUIMIMIMIMIGEGEGEGEGoQeGHr0k//8MH////nAv//5sL2fC/v9f+eC////4TVQmmmqYTCaYTTTTTVNU9el/82mZI////9Eb/m0/+iN/M9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/c1XH///Bh/3/giPzj/f2/giPzj/f/+1/51f9v/2v/7/2v9//vX/hv9v5Dsuz8R2cI/EcZ+/zxH7IQj8R358j8ejcXM0f5iI7LxuLmEGeInf/niCDCDPEfiO38+ROQQZiI7OEfi5no+X8f5u86ftpN/4hxhBx+9YQfhBx6QQf6YT/j9PQYT96wg/Qcd+gwnxhB6eE/2v///f/Lty+Njl40u10bH6LvL0FIu/xKhouB6y+xKhhhI1tFwPtdGxhhBhI1uXPIPlGtou2GFL4u8Soei7f4YX/7a7aRUwdv/vpP//pP9N/Tf09PpfT6T0/+k/pPt+k9X9N0/V4iIiI/DS4YUEDv/TdfX7//pN1aTf1pPHXXpNpPv/+k3X6TaT9JN16T/IPYrDFBA2//4+2l2lj//NN///X///aWP//NV/H//x/DC2EEG3//C//4X/9v//4X/x//hfj/4///8RERCbf/8zC//5sL/+///zVf///Nhf/////0G//9Eb9tLbS0Rv///////mD+2lojf82n+YP//+m3//Rff/9F9/////////0X3//8/f/+fsIO//9f//X/+v////2fv/6/s/f9n7///9EHIbf+0vXYrevX20vSbS19tf/3SbXevX3S690m0vbS/bS0EDbfkEja9r/H2vnK147W1+1//tbXj7X7XU9SGL2tr9r/a6CDbf+K2JQ//2JQ/9iumKYlD/Y//hsUx/sSh8OGxX/sUxUPYr9is7Jg5cHPkcByGNBNsL/M4W03tbW03+GFwgwmvwy3/+4YQZb9rabdwwuvwwgwnwwvwwvwg8IOg21xERERHERERFxERERERERERERERERERURERERERHOxvnqNzM9F40nr/X6Cb4QdJtrpa/76TdNwvpV/j+k2PCQpf3pNpP6S/ZLT+3xH5BOIh/0//Bh/q3/nV/6vmROIWMzjtVGSsyPE0iFjIVEEM7FIlF+G/9+ZDPCDySYQa5WfCD1/86ftpJP109QmgWunoF///9/FE8jonzocUTyND/trtpb/hN6CD/Cb/+Glwwvf9+r/f/kHsVhit+dkhH4uwgzEfI/FzxNx9l43FzNCCDMR6NxHZwto+R+LmEGYjcR2Xi5mzNCCDMR8j8XPE+R+I4z8Xj5GhBBniJyOsR2cInZeNx6NxuI7OETv/DC2F/CD0H4QeneE/TCD/jQaQhB6fx6YTCD8IPTtBxBEfrCaD0Gg40GE/+NBhPxERHfou8SoYYWi7xKhsPRcDiVDRsYYXy/Nb0XeJUMMLl8JUCeGjYwwtF3iVDYaNbl410XbRrYYSNbRrcvzW0XA/5fmtouB/+m6f6bp2RaaenSf/Sfpun/pp0n+m6dkWif/q0n0nSfSen/0np/vpN036TdYYPSev/6Sb0m6/qq/9JusMGk3T1pOk3pNpN0k2k/9JNpP////8iC/8f/////x//yIL+18f//////9////g//hf+P///wv/4PH/8fHHx//x/////9v/zgX/8hr///OBf/2/ev/////7////lF/+iN//MDcP///RG//8oubT/zA8wZgeYP/8wfzsxFEaRpm8nzqRwHP///////0X3/9L///Rff//+18/f////9r94Qfv////v/+v/s/Wv///X//fZ+3r+z92frP3Z+//s/f+giP96NzM8Lf20rS9tL2GEttf19d0mGk2l//+vtpewwk6X+2k6W6TpbpNrr7pNrwiPoER//0E3r/tbX7X2Ptfte17Vjtf//tftfY7Xj7W17W17W1tftbU5f//CI/74QIj9pdioRHHD2K9rYlD/YlD4bEofsUwmK//9iUPh7Fe0xW1sUxWxTFbFMSh7Eof7FMf/6Tft+PW38MLYT4YXhhYa/abdrwwgwgwv//abfDC8MIML8MIMLDCDCwwg1tfhhBlv/bS//7x/44iIiIiOIiIiIiIiIiIiIiIiIiIiIiIiIjiIiIiP/rft7Jadv+dgV//0/t7Ih/aX9L9tL//Bi9/7a//oX7eRAEdr+K///bwg9uvhhftpc//8nwg9teIj/6Tft+g+0v//+xsXtpf2K//vbX/6TfvG2l///cIhie0viIiIiKBA2wwl9BBthr9INsMJfSbZDP6+EE4YhfSba+k2187MZqGSCSN5PmYZrCNkRhEiKczQjMjMzQv4QYQaCvCzjhBBgg9BppoNP+i8aNzCV6LwggcMJG5ovmgiPo1tGto1tGto1v/CDoJtL4QbwYJBNoJ/Senp0nQTf9JvdYRH9Lygt6b+m0m0m69//4oIj+37ewWOKTf/44/9Ju0F/pB/tL//0v/7JH3t+37JVVv/88aX/2RPvt+yGLyCcRCNPa/9//wYbf/nx/gw/21/yjS/+Q5i9v9kSmdX0LYgiP/7aXOxgUqBnYnFKjtY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HhBx8Qwgwg8IMIPjCDiHhBhBxD8IOIfhBhBxD8IP4hhBxhBhB8Q8IOIemnhDiGn/GEH+aM0Z4zRni1PGE1NGeM8eeM8WoU8Zo88Z4zRa54zR+eM8Zo/PFpqaM8YU8Z480eeM0WkeGjw+eM0UZ218KeP6bptJum0m9Juum0m0m9JtJvpJum9JtJum/SbptJtJtJum20km/ptJukm0m+m9Jum+xsdJ6bF/6Sb///8f//HHx4//+P///Hj////Hx4/8f7DYf/gv/H/////////////9P/20v/////w2G//CM//////Pp//n0PTPSCBf/+c///+f+CBf///z6Z6QQL/np8Nhv/0F/8+yv7S9tL2zftpf7ZvbN+2b20vbS/bSbStL20v9tJtK0o20v9s37ZvbS7S2zf+e8/+2v67a7Zv/H8fxXH38VFcVHfH/HHHfH5jSbjjjaj7+K4qPj4r7YpiuP4u4+K/OOF4YXhhYYW/hhBhYYQYT4YX4YQYU44T4YX/hhBhTjhYYW/hhYYQYXOOFhhe7TWGCI/7Le4YIj+GF/EREREREREREREREREcRERERERxoREREREcREREREEfIiIiIiP//bS/+F4//1hhfEkZfL5/JPJRlYy+RGKQkZlGeyJYng5SZ1xOGUmR//+v+dqn+X8gX+mEGv8f6x/xHHyBegRfRsdD//////p/51zebyP/keM5MwzGezcZ2TxzI+eyPGI8zfnIzs/nMvkeM45keJ8j5uM4j5fOZyPyeR43nM5nMj5uN6ZHy+R4ziPp5HjOz2R8wz9m/0hNxzI8mR8vkfI+R4xf//iGEHp2oQYQwg44tNPwgwh/EMIOIcYQYQ/CDT4h/+n/EMIfEMIPjTCD0mKwg4h/HFp/b0v5ozxaRnbaU8Z4zxhTOJTtGd/PGePzRnjNGFPGePzxR4bU0f/R4e1zRni1NGePCRnc8dKjfzxmi1wpnEp3/T/6bSbxdikm0nSbqmnF/SbSf6bSbpukm0n9Jux6bbSbS7H+m0n6bSb6F0m9JP0m6b+qaf///x4K1H//4L8f///x/8bD//9h///8fguMUgRHkv///+xBAhX/+EZ///+EZ///////hv9tJtLDf///+EZ/dMf//////59KC056f/9Bfz0///89P8+j//8N///86foLwRxwRf2mwQL///+9f2k2b/VGdtm9tW0v/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FUdEh+v/9V+aCrrmsX1rWta+RJ+RNYr1X/jrS+NhpHw6XsOv6r+mH//8daVdaXx9GgeuFZf66/r/zYeuMkB0uYf8w/5h/7D9h1X/yi/5Id/4b10sN/6+l4b/S//////Xkh2k3Dyi///0tf19dLXS10ksNpYb//2mq+l6rg3ojj6QN6pda6Sg2qrqqrqvqq6r6ojjpfFWnqqpKqquqI466I4/X9f1+kDeoN+qqvSqpYc9V9VLA/Bf4fr16+g9VXVVUsNV9VXVSyaqC89V76XVV1VVXUF9Sy4L44qOKjivD9B89dVVWGv+nVpfkQ3S0ktTDe1+rXXOrf+1//StfS//S06vaYa2v//2ulr6SVPT0+phval2+na/4r2zk2lv+2crX19bVtLY20te1/tL9s5ftpf+2cv1bS3bWKbS//+0tbVs5a7775x62tpWphtpNpf2vi4r5FfxbFSK45FcbFRXUVHsV/FfxfyKgV8iv4v44r4tqK//+KjY4uORSFVVVcbFRTFcVFf2EkkwmkrpJJhNU9SKOmmktqRR0k0kkkix0kkkwkkqaSSpJJhKk7SVshR7CaSSSSSSSSRY6dphPX/6UijppFjppJppJJKGF4YQYW4fwwgwg7JvDsLDCDC6DCYXhhf4a/DC8OGF4fwwq2TeGFuGE0GEGF//+GmTey3hhMm8PVVVVsLDCw0GFhhBhfxEREREREREREREcRERERxERERERERERERERERERERERxERxERERERERHEREREaXkaf1/S4JJJRpa9r14YXEREf//////////////////gAgAgNCmVuZHN0cmVhbQ0KZW5kb2JqDQo2IDAgb2JqDQozMDUzNA0KZW5kb2JqDQoxIDAgb2JqDQo8PA0KL1R5cGUgL0NhdGFsb2cNCi9QYWdlcyAzIDAgUg0KPj4NCmVuZG9iag0KMiAwIG9iag0KWyAvUERGIC9JbWFnZUIgIF0NCmVuZG9iag0KNyAwIG9iag0KPDwgL0xlbmd0aCA1MiA+Pg0Kc3RyZWFtDQowMDA2MTIgMCAwIDAwMDc5MiAgICAgIDAgICAgICAwIGNtDQovU25vd2JvdW5kMCBEbw0KZW5kc3RyZWFtDQplbmRvYmoNCjQgMCBvYmoNCjw8DQovVHlwZSAvUGFnZQ0KL1BhcmVudCAzIDAgUg0KL1Jlc291cmNlcyA8PA0KL1hPYmplY3QgPDwNCi9Tbm93Ym91bmQwIDUgMCBSDQo+Pg0KL1Byb2NTZXQgMiAwIFINCj4+DQovQ29udGVudHMgNyAwIFINCj4+DQplbmRvYmoNCjMgMCBvYmoNCjw8DQovVHlwZSAvUGFnZXMNCi9LaWRzIFs0IDAgUiANCl0NCi9Db3VudCAxDQovTWVkaWFCb3ggWyAwIDAgNjEyIDc5MiBdDQo+Pg0KZW5kb2JqDQp4cmVmDQowIDgNCjAwMDAwMDAwMDAgNjU1MzUgZg0KMDAwMDAzMDg5MyAwMDAwMCBuDQowMDAwMDMwOTQ4IDAwMDAwIG4NCjAwMDAwMzEyMzEgMDAwMDAgbg0KMDAwMDAzMTA5MCAwMDAwMCBuDQowMDAwMDAwMDEwIDAwMDAwIG4NCjAwMDAwMzA4NjkgMDAwMDAgbg0KMDAwMDAzMDk4NCAwMDAwMCBuDQp0cmFpbGVyDQo8PA0KL1NpemUgOA0KL1Jvb3QgMSAwIFINCj4+DQpzdGFydHhyZWYNCjMxMzI1DQolJUVPRg0K</SignatureConfirmationLabel><ToName>D K</ToName><ToFirm>ASDAS</ToFirm><ToAddress1>43514 Christy Street</ToAddress1><ToAddress2>43514 CHRISTY ST</ToAddress2><ToCity>FREMONT</ToCity><ToState>CA</ToState><ToZip5>94538</ToZip5><ToZip4>3294</ToZip4><Postnet>94538329414</Postnet></SigConfirmCertifyV3.0Response>
    ";

    public function _prepare($mode = 'rates')
    {
        $params = array(
            'http_responce' => ''
        );
        switch ($mode) {
            case "rates":
                $params['http_responce'] = self::SUCCESS_USPS_RESPONSE_RATES;
                break;
            case 'rma':
                $params['http_responce'] = self::SUCCESS_USPS_RESPONSE_RMA;
                break;
        }

        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /**
         * \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Shipping\Model\Rate\Result\ErrorFactory $rateErrorFactory,
        \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory,
        \Magento\Usa\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Shipping\Model\Rate\Result\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Usa\Helper\Data $usaData,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollFactory,
        array $data = array()
         */
        $coreStoreConfig = $this->getMockBuilder('\Magento\Core\Model\Store\Config')
            ->setMethods(array('getConfigFlag', 'getConfig'))
            ->disableOriginalConstructor()
            ->getMock();
        $coreStoreConfig->expects($this->any())
            ->method('getConfigFlag')
            ->will($this->returnValue(true));
        $coreStoreConfig->expects($this->any())
            ->method('getConfig')
            ->will($this->returnCallback(array($this, 'coreStoreConfigGetConfig')));

        $rateErrorFactory = $this->getMockBuilder('\Magento\Shipping\Model\Rate\Result\ErrorFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $logAdapterFactory = $this->getMockBuilder('\Magento\Core\Model\Log\AdapterFactory')
            ->disableOriginalConstructor()
            ->getMock();

        // xml element factory

        $xmlElFactory = $this->getMockBuilder('\Magento\Usa\Model\Simplexml\ElementFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $xmlElFactory->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(
                function($data){
                    $oM = new \Magento\TestFramework\Helper\ObjectManager($this);
                    return  $oM->getObject('\Magento\Usa\Model\Simplexml\Element', array('data' => $data['data']));
                }));


        // rate factory
        $rateFactory = $this->getMockBuilder('\Magento\Shipping\Model\Rate\ResultFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $rateResult = $this->getMockBuilder('\Magento\Shipping\Model\Rate\Result')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $rateFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($rateResult));

        // rate method factory
        $rateMethodFactory = $this->getMockBuilder('\Magento\Shipping\Model\Rate\Result\MethodFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $rateMethod = $this->getMockBuilder('Magento\Shipping\Model\Rate\Result\Method')
            ->disableOriginalConstructor()
            ->setMethods(array('setPrice'))
            ->getMock();
        $rateMethod->expects($this->any())
            ->method('setPrice')
            ->will($this->returnSelf());

        $rateMethodFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($rateMethod));

        $trackFactory = $this->getMockBuilder('\Magento\Shipping\Model\Tracking\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $trackErrorFactory = $this->getMockBuilder('\Magento\Shipping\Model\Tracking\Result\ErrorFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $trackStatusFactory = $this->getMockBuilder('\Magento\Shipping\Model\Tracking\Result\StatusFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $regionFactory = $this->getMockBuilder('\Magento\Directory\Model\RegionFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $countryFactory = $this->getMockBuilder('\Magento\Directory\Model\CountryFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $currencyFactory = $this->getMockBuilder('\Magento\Directory\Model\CurrencyFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $directoryData = $this->getMockBuilder('\Magento\Directory\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $usaData = $this->getMockBuilder('\Magento\Usa\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $productCollFactory = $this->getMockBuilder('\Magento\Catalog\Model\Resource\Product\CollectionFactory')
            ->disableOriginalConstructor()
            ->getMock();

        // http client
        $httpResponse = $this->getMockBuilder('\Zend_Http_Response')
            ->disableOriginalConstructor()
            ->setMethods(array('getBody'))
            ->getMock();
        $httpResponse->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($params['http_responce']));

        $httpClient = $this->getMockBuilder('\Zend_Http_Client')
            ->disableOriginalConstructor()
            ->setMethods(array('request'))
            ->getMock();
        $httpClient->expects($this->any())
            ->method('request')
            ->will($this->returnValue($httpResponse));

        $httpClientFactory = $this->getMockBuilder('\Zend_Http_ClientFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();

        $httpClientFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($httpClient));

        $data = array(
            'id' => 'usps',
            'store' => '1'
        );

        $arguments = array(
            'coreStoreConfig' => $coreStoreConfig,
            'rateErrorFactory' => $rateErrorFactory,
            'logAdapterFactory' => $logAdapterFactory,
            'xmlElFactory' => $xmlElFactory,
            'rateFactory' => $rateFactory,
            'rateMethodFactory' => $rateMethodFactory,
            'trackFactory' => $trackFactory,
            'trackErrorFactory' => $trackErrorFactory,
            'trackStatusFactory' => $trackStatusFactory,
            'regionFactory' => $regionFactory,
            'countryFactory' => $countryFactory,
            'currencyFactory' => $currencyFactory,
            'directoryData' => $directoryData,
            'usaData' => $usaData,
            'productCollFactory' => $productCollFactory,
            'httpClientFactory' => $httpClientFactory,
            'data' => $data
        );

        $this->_model = $this->_helper->getObject('\Magento\Usa\Model\Shipping\Carrier\Usps', $arguments);
    }

    /**
     * @dataProvider codeProvider
     */
    public function testGetCodeArray($code)
    {
        $this->_prepare();
        $this->assertNotEmpty($this->_model->getCode($code));
    }

    public function testGetCodeBool()
    {
        $this->_prepare();
        $this->assertFalse($this->_model->getCode('test_code'));
    }

    public function testCollectRates()
    {
        $this->_prepare('rates');
        // for setRequest
        $request = $this->_helper->getObject('Magento\Shipping\Model\Rate\Request',
            array(
                'data' => array(
                    'dest_country_id' => 'US',
                    'dest_region_id' => '12',
                    'dest_region_code' => 'CA',
                    'dest_street' => 'main st1',
                    'dest_city' => 'Los Angeles',
                    'dest_postcode' => '90032',
                    'package_value' => '5',
                    'package_value_with_discount' => '5',
                    'package_weight' => '5',
                    'package_qty' => '1',
                    'package_physical_value' => '5',
                    'free_method_weight' => '5',
                    'store_id' => '1',
                    'website_id' => '1',
                    'free_shipping' => '0',
                    'limit_carrier' => 'null',
                    'base_subtotal_incl_tax' => '5',
                    'orig_country_id' => 'US',
                    'country_id' => 'US',
                    'region_id' => '12',
                    'city'=> 'Culver City',
                    'postcode' => '90034',
                    'usps_userid' => '213MAGEN6752',
                    'usps_container' => 'VARIABLE',
                    'usps_size' => 'REGULAR',
                    'girth' => null,
                    'height' => null,
                    'length' => null,
                    'width' => null,
                )
            )
        );

        $this->assertNotEmpty($this->_model->collectRates($request)->getAllRates());
    }

    public function testReturnOfShipment()
    {
        $this->_prepare('rma');
        $request_params = array(
            'data' => array(
                'shipper_contact_person_name' => 'testO',
                'shipper_contact_person_first_name' => 'test ',
                'shipper_contact_person_last_name' => 'O',
                'shipper_contact_company_name' => 'testO',
                'shipper_contact_phone_number' => '23424',
                'shipper_email' => 'test@domain.ru',
                'shipper_address_street' => 'mainst1',
                'shipper_address_street1' => 'mainst1',
                'shipper_address_street2' => '',
                'shipper_address_city' => 'Los Angeles',
                'shipper_address_state_or_province_code' => 'CA',
                'shipper_address_postal_code' => '90032',
                'shipper_address_country_code' => 'US',
                'recipient_contact_person_name' => 'DK',
                'recipient_contact_person_first_name' => 'D',
                'recipient_contact_person_last_name' => 'K',
                'recipient_contact_company_name' => 'wsdfsdf',
                'recipient_contact_phone_number' => '234324',
                'recipient_email' => '',
                'recipient_address_street' => '43514 Christy Street',
                'recipient_address_street1' => '43514 Christy Street',
                'recipient_address_street2' => '43514 Christy Street',
                'recipient_address_city' => 'Fremont',
                'recipient_address_state_or_province_code' => 'CA',
                'recipient_address_region_code' => 'CA',
                'recipient_address_postal_code' => '94538',
                'recipient_address_country_code' => 'US',
                'shipping_method' => '6',
                'package_weight' => '5',
                'base_currency_code' => 'USD',
                'store_id' => '1',
                'reference_data' => 'RMA #100000001 P',
                'packages' => array(
                    1 => array(
                        'params' => array('container'=>'','weight'=>5,'custom_value'=>'','length'=>'','width'=>'','height'=>'','weight_units'=>'POUND','dimension_units'=>'INCH','content_type'=>'','content_type_other'=>'','delivery_confirmation'=>'True'),
                        'items' => array(
                            '2' => array('qty'=>'1','customs_value'=>'5','price'=>'5.0000','name'=>'prod1','weight'=>'5.0000','product_id'=>'1','order_item_id'=>2)
                        )
                    )
                ),
                'order_shipment' => null
            )
        );
        $request = $this->_helper->getObject('Magento\Shipping\Model\Shipment\ReturnShipment', $request_params);
        $this->assertNotEmpty($this->_model->returnOfShipment($request)->getInfo()[0]['tracking_number']);

    }


    public function coreStoreConfigGetConfig($path, $store)
    {
        switch ($path) {
            case 'carriers/usps/allowed_methods':
                return '0_FCLE,0_FCL,0_FCP,1,2,3,4,6,7,13,16,17,22,23,25,27,28,33,34,35,36,37,42,43,53,55,56,57,61,INT_1,INT_2,INT_4,INT_6,INT_7,INT_8,INT_9,INT_10,INT_11,INT_12,INT_13,INT_14,INT_15,INT_16,INT_20,INT_26';
            default:
                return null;
        }
    }

    public function codeProvider()
    {
        return array(
            array('container'),
            array('machinable'),
            array('method'),
            array('size')
        );
    }

}
