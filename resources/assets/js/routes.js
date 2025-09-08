import IndexLogin from '@/components/login/Index'
import Profile from '@/components/login/Profile'
import DashboardIndex from '@/components/dashboard/Index'
import UserIndex from '@/components/user/Index'
import RoleIndex from '@/components/role/Index'
import ProcedureTypeLoanDestiny from '@/components/procedure_type/LoanDestiny'
import AffiliateIndex from '@/components/affiliate/Index'
import AffiliateAdd from '@/components/affiliate/Add'
import LoanAdd from '@/components/loan/Add'
import RecordIndex from '@/components/record/Index'
import Camara from '@/components/affiliate/Webcam'
import FlowIndex from '@/components/workflow/Index'
import FlowAdd from '@/components/workflow/Add'
import PaymentAdd from '@/components/payment/Add'
import LoanPaymentIndex from '@/components/payment/Index'
import ImportExportPayment from '@/components/payment/ImportExportPayment'
import LoansGenerateIndex from '@/components/payment/LoansGenerateIndex'
import ListPaymentGenerate from '@/components/payment/ListPaymentGenerate'
import ChangeRol from '@/components/dashboard/ChangeRol'
import ListVouchers from '@/components/treasury/ListVouchers'
import Reports from '@/components/reports/Reports'
import ListTracingLoans from '@/components/tracing/ListTracingLoans'
import TracingAdd from '@/components/tracing/Add'
import FundRotaryList from '@/components/fund_rotary/ListEntry'
import SismuIndex from '@/components/sismu/Index'
import WorkFlowByArea from '@/components/procedure_type/WorkFlowByArea'
import ModalityFlow from '@/components/procedure_type/ModalityFlow'

export const routes = [
  {
    name: 'cam',
    path: '/cam',
    component: Camara,
    meta: {
      requiresAuth: false
    }
  },
  {
    name: 'login',
    path: '/login',
    component: IndexLogin
  }, {
    name: 'profile',
    path: '/profile',
    component: Profile,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '*',
    redirect: {
      name: 'changeRol'
    },
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/changeRol',
    name: 'changeRol',
    component: ChangeRol,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/dashboard',
    name: 'dashboardIndex',
    component: DashboardIndex,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/sismu',
    name: 'sismuIndex',
    component: SismuIndex,
    meta: {
      requiresAuth: true
    }
  },
  //Adminsitracion
  {
    path: '/user',
    name: 'userIndex',
    component: UserIndex,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/role',
    name: 'roleIndex',
    component: RoleIndex,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/procedure_type/loan_destiny',
    name: 'procedureTypeLoanDestiny',
    component: ProcedureTypeLoanDestiny,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/procedure_type/workflow_area',
    name: 'workFlowByArea',
    component: WorkFlowByArea,
    meta: {
      requiresAuth: true
    }, 
  }, {
    path: '/procedure_type/modality_flow',
    name: 'modalityFlow',
    component: ModalityFlow,
    meta: {
      requiresAuth: true
    }, 
  },
  // 
  {
    path: '/affiliate',
    name: 'affiliateIndex',
    component: AffiliateIndex,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/affiliate/:id',
    name: 'affiliateAdd',
    component: AffiliateAdd,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/record',
    name: 'recordIndex',
    component: RecordIndex,
    meta: {
      requiresAuth: true
    }
  }, {
    path: '/loan/:hash',
    name: 'loanAdd',
    component: LoanAdd,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/importExportPayment',
    name: 'ImportExportPayment',
    component: ImportExportPayment,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/workflow',
    name: 'flowIndex',
    component: FlowIndex,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/workflow/:id',
    name: 'flowAdd',
    component: FlowAdd,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/loansGenerate',
    name: 'loansGenerateIndex',
    component: LoansGenerateIndex,
    meta: {
      requiresAuth: true
    }
  },
  //Cobros
  {
    path: '/kardex/:hash',
    name: 'paymentAdd',
    component: PaymentAdd,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/loanPayment',
    name: 'loanPaymentIndex',
    component: LoanPaymentIndex,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/listPaymentGenerate',
    name: 'listPaymentGenerate',
    component: ListPaymentGenerate,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/listVouchers',
    name: 'listVouchers',
    component: ListVouchers,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/reports',
    name: 'reports',
    component: Reports,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/ListTracingLoans',
    name: 'ListTracingLoans',
    component: ListTracingLoans,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/tracingLoan/:id',
    name: 'tracingAdd',
    component: TracingAdd,
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/fundRotary',
    name: 'fundRotaryList',
    component: FundRotaryList,
    meta: {
      requiresAuth: true
    }
  },
]
