export default class WfState {
  constructor() {
    this.resource = `wf_state`
  }

  async get(id = null) {
    try {
      let res = await axios.get(id ? `${this.resource}/${id}` : this.resource)
      return res.data
    } catch (e) {
      return e
    }
  }
}